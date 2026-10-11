<?php

namespace App\Controllers;

use App\Services\LedgerService;
use Config\Database;
use RuntimeException;

class LedgerController extends BaseController
{
    protected $db;
    protected LedgerService $ledger;

    public function __construct()
    {
        $this->db     = Database::connect();
        $this->ledger = new LedgerService();
    }

    public function index()
    {
        $this->ledger->ensureCoreChart();
        $this->ledger->backfillFromJournals();

        $type = strtoupper((string) $this->request->getGet('type'));
        $labels = $this->ledger->typeLabels();
        if (! isset($labels[$type])) {
            $type = '';
        }

        $branchId = $this->branchScope();

        $builder = $this->db->table('transactions')
            ->select('transactions.*, accounts.account_no, customers.full_name, users.username as created_by_name, to_accounts.account_no as counter_account_no, to_customers.full_name as counter_name')
            ->join('accounts', 'accounts.id = transactions.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('accounts to_accounts', 'to_accounts.id = transactions.counter_account_id', 'left')
            ->join('customers to_customers', 'to_customers.id = to_accounts.customer_id', 'left')
            ->join('users', 'users.id = transactions.created_by', 'left')
            ->orderBy('transactions.id', 'DESC');

        if ($type !== '') {
            $builder->where('transactions.type', $type);
        }
        if ($branchId !== null) {
            $builder->where('transactions.branch_id', $branchId);
        }

        $entries = $builder->limit(100)->get()->getResultArray();

        $totalsBuilder = $this->db->table('transaction_entries')
            ->select('COALESCE(SUM(transaction_entries.debit), 0) as total_debit, COALESCE(SUM(transaction_entries.credit), 0) as total_credit');
        if ($branchId !== null) {
            $totalsBuilder->join('transactions', 'transactions.id = transaction_entries.transaction_id')
                ->where('transactions.branch_id', $branchId);
        }
        $totals = $totalsBuilder->get()->getRowArray();

        $depositBuilder = $this->db->table('accounts')
            ->select('COALESCE(SUM(balance), 0) as deposits, COUNT(*) as account_count')
            ->where('status', 'Active');
        if ($branchId !== null) {
            $depositBuilder->where('branch_id', $branchId);
        }
        $depositRow = $depositBuilder->get()->getRowArray();

        return view('ledger/index', $this->sessionView() + [
            'entries'      => $entries,
            'typeLabels'   => $labels,
            'activeType'   => $type,
            'canPost'      => $this->canPost(),
            'totalDebit'   => (float) ($totals['total_debit'] ?? 0),
            'totalCredit'  => (float) ($totals['total_credit'] ?? 0),
            'depositTotal' => (float) ($depositRow['deposits'] ?? 0),
            'accountCount' => (int) ($depositRow['account_count'] ?? 0),
        ]);
    }

    public function create()
    {
        if (! $this->canPost()) {
            return redirect()->to('/ledger')->with('error', 'You do not have permission to post transactions.');
        }

        return view('ledger/create', $this->sessionView() + [
            'typeLabels' => $this->postableTypes(),
            'accounts'   => $this->depositAccounts(),
            'loans'      => $this->activeLoans(),
        ]);
    }

    public function store()
    {
        if (! $this->canPost()) {
            return redirect()->to('/ledger')->with('error', 'You do not have permission to post transactions.');
        }

        $type = strtoupper((string) $this->request->getPost('type'));
        if (! isset($this->postableTypes()[$type])) {
            return redirect()->back()->withInput()->with('error', 'Choose a transaction type.');
        }

        $rules = [
            'account_id' => 'required|is_natural_no_zero',
            'amount'     => 'required|decimal|greater_than[0]|less_than[100000000]',
            'tx_date'    => 'required|valid_date[Y-m-d]',
            'narration'  => 'permit_empty|max_length[255]',
        ];
        if ($type === 'LOAN_REPAYMENT') {
            $rules['loan_id'] = 'required|is_natural_no_zero';
        }
        if ($type === 'ADJUSTMENT') {
            $rules['adjustment_side'] = 'required|in_list[credit,debit]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Enter an account, a date, and an amount greater than zero.');
        }

        try {
            $this->assertAccountInBranch((int) $this->request->getPost('account_id'));
            $posted = $this->ledger->postCustomerTransaction(
                $type,
                (int) $this->request->getPost('account_id'),
                (float) $this->request->getPost('amount'),
                (string) $this->request->getPost('narration'),
                session()->get('user_id') ? (int) session()->get('user_id') : null,
                (string) $this->request->getPost('tx_date'),
                (string) $this->request->getPost('adjustment_side'),
                $this->request->getPost('loan_id') ? (int) $this->request->getPost('loan_id') : null
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Transaction failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'The transaction could not be posted. Please try again.');
        }

        $label = $this->ledger->typeLabels()[$type] ?? $type;

        return redirect()->to('/ledger/' . $posted['transaction_id'])->with(
            'success',
            $label . ' ' . $posted['tx_id'] . ' posted. The ledger entries are balanced.'
        );
    }

    public function show($id = null)
    {
        $entry = $this->db->table('transactions')
            ->select('transactions.*, accounts.account_no, customers.full_name, users.username as created_by_name, branches.name as branch_name, to_accounts.account_no as counter_account_no, to_customers.full_name as counter_name')
            ->join('accounts', 'accounts.id = transactions.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('accounts to_accounts', 'to_accounts.id = transactions.counter_account_id', 'left')
            ->join('customers to_customers', 'to_customers.id = to_accounts.customer_id', 'left')
            ->join('users', 'users.id = transactions.created_by', 'left')
            ->join('branches', 'branches.id = transactions.branch_id', 'left')
            ->where('transactions.id', $id)
            ->get()
            ->getRowArray();

        if (! $entry || ! $this->transactionInScope($entry)) {
            return redirect()->to('/ledger')->with('error', 'Transaction not found.');
        }

        $lines = $this->db->table('transaction_entries')
            ->select('transaction_entries.*, gl_accounts.code as gl_code, gl_accounts.name as gl_name, accounts.account_no, customers.full_name')
            ->join('gl_accounts', 'gl_accounts.id = transaction_entries.gl_account_id')
            ->join('accounts', 'accounts.id = transaction_entries.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->where('transaction_entries.transaction_id', $entry['id'])
            ->orderBy('transaction_entries.id', 'ASC')
            ->get()
            ->getResultArray();

        return view('ledger/show', $this->sessionView() + [
            'entry'      => $entry,
            'lines'      => $lines,
            'typeLabels' => $this->ledger->typeLabels(),
        ]);
    }

    public function chart()
    {
        $this->ledger->ensureCoreChart();
        $this->ledger->backfillFromJournals();

        $accounts = $this->db->table('gl_accounts')->orderBy('code', 'ASC')->get()->getResultArray();
        $sums = $this->db->table('transaction_entries')
            ->select('gl_account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('gl_account_id')
            ->get()
            ->getResultArray();

        $byGl = [];
        foreach ($sums as $sum) {
            $byGl[$sum['gl_account_id']] = $sum;
        }

        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        foreach ($accounts as &$account) {
            $debit  = (float) ($byGl[$account['id']]['total_debit'] ?? 0);
            $credit = (float) ($byGl[$account['id']]['total_credit'] ?? 0);
            $account['total_debit']  = $debit;
            $account['total_credit'] = $credit;
            $account['balance']      = $account['normal_balance'] === 'DEBIT'
                ? round($debit - $credit, 2)
                : round($credit - $debit, 2);
            $totalDebit  += $debit;
            $totalCredit += $credit;
        }
        unset($account);

        return view('ledger/chart', $this->sessionView() + [
            'accounts'    => $accounts,
            'totalDebit'  => round($totalDebit, 2),
            'totalCredit' => round($totalCredit, 2),
        ]);
    }

    protected function sessionView(): array
    {
        return [
            'username' => session()->get('username'),
            'role'     => session()->get('role'),
        ];
    }

    protected function canPost(): bool
    {
        return in_array(session()->get('role'), ['SUPER ADMIN', 'Branch Manager', 'Account Officer', 'Teller'], true);
    }

    /**
     * @return array<string, string>
     */
    protected function postableTypes(): array
    {
        $labels = $this->ledger->typeLabels();
        $allowed = ['DEPOSIT', 'WITHDRAWAL', 'LOAN_REPAYMENT', 'INTEREST_CREDIT', 'FEE', 'ADJUSTMENT'];
        return array_intersect_key($labels, array_flip($allowed));
    }

    protected function depositAccounts(): array
    {
        $builder = $this->db->table('accounts')
            ->select('accounts.*, customers.full_name, customers.customer_id as customer_code')
            ->join('customers', 'customers.id = accounts.customer_id')
            ->where('accounts.status', 'Active')
            ->orderBy('accounts.account_no', 'ASC');

        if ($this->branchScope() !== null) {
            $builder->where('accounts.branch_id', $this->branchScope());
        }

        return $builder->get()->getResultArray();
    }

    protected function branchScope(): ?int
    {
        if (session()->get('role') === 'SUPER ADMIN') {
            return null;
        }

        return (int) session()->get('branch_id');
    }

    protected function assertAccountInBranch(int $accountId): void
    {
        $account = $this->db->table('accounts')->select('id, branch_id')->where('id', $accountId)->get()->getRowArray();
        $scope = $this->branchScope();
        if (! $account || ($scope !== null && (int) $account['branch_id'] !== $scope)) {
            throw new RuntimeException('That account belongs to another branch.');
        }
    }

    protected function transactionInScope(array $entry): bool
    {
        $scope = $this->branchScope();

        return $scope === null || (int) ($entry['branch_id'] ?? 0) === $scope;
    }

    protected function activeLoans(): array
    {
        $builder = $this->db->table('loans')
            ->select('loans.id, loans.loan_account_no, loans.outstanding_balance, loans.customer_id, customers.full_name')
            ->join('customers', 'customers.id = loans.customer_id')
            ->where('loans.status', 'Active')
            ->orderBy('loans.id', 'DESC');

        if ($this->branchScope() !== null) {
            $builder->where('loans.branch_id', $this->branchScope());
        }

        return $builder->get()->getResultArray();
    }
}
