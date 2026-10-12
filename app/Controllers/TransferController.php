<?php

namespace App\Controllers;

use App\Services\LedgerService;
use Config\Database;
use RuntimeException;

class TransferController extends BaseController
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
        return view('transfers/index', [
            'username'  => session()->get('username'),
            'role'      => session()->get('role'),
            'accounts'  => $this->depositAccounts(),
            'transfers' => $this->recentTransfers(),
        ]);
    }

    public function store()
    {
        $fromId = (int) $this->request->getPost('from_account_id');
        $toId   = (int) $this->request->getPost('to_account_id');
        if ($fromId > 0 && $fromId === $toId) {
            return redirect()->back()->withInput()->with('error', 'Choose two different accounts.');
        }

        $rules = [
            'from_account_id' => 'required|is_natural_no_zero',
            'to_account_id'   => 'required|is_natural_no_zero',
            'amount'          => 'required|decimal|greater_than[0]|less_than[100000000]',
            'narration'       => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Enter two accounts and an amount greater than zero.');
        }

        try {
            $this->assertAccountsInBranch($fromId, $toId);
            $result = $this->ledger->transfer(
                (int) $this->request->getPost('from_account_id'),
                (int) $this->request->getPost('to_account_id'),
                (float) $this->request->getPost('amount'),
                (string) $this->request->getPost('narration'),
                session()->get('user_id') ? (int) session()->get('user_id') : null
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Transfer failed: {message}', ['message' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Transfer could not be posted. Please try again.');
        }

        return redirect()->to('/transfers')->with(
            'success',
            'Transfer ' . $result['transfer_no'] . ' posted as ' . $result['tx_id'] . '. The ledger entries are balanced.'
        );
    }

    protected function depositAccounts(): array
    {
        if (! $this->db->tableExists('accounts')) {
            return [];
        }

        $builder = $this->db->table('accounts')
            ->select('accounts.*, customers.full_name, customers.customer_id as customer_code, gl_accounts.code as gl_code')
            ->join('customers', 'customers.id = accounts.customer_id')
            ->join('gl_accounts', 'gl_accounts.id = accounts.gl_account_id')
            ->where('accounts.status', 'Active')
            ->orderBy('customers.full_name', 'ASC')
            ->orderBy('accounts.account_no', 'ASC');

        if (session()->get('role') !== 'SUPER ADMIN') {
            $builder->where('accounts.branch_id', (int) session()->get('branch_id'));
        }

        return $builder->get()->getResultArray();
    }

    protected function assertAccountsInBranch(int $fromId, int $toId): void
    {
        if (session()->get('role') === 'SUPER ADMIN') {
            return;
        }

        $scope = (int) session()->get('branch_id');
        $rows = $this->db->table('accounts')->select('id, branch_id')->whereIn('id', [$fromId, $toId])->get()->getResultArray();
        if (count($rows) !== 2) {
            throw new RuntimeException('One of the accounts could not be found.');
        }
        foreach ($rows as $row) {
            if ((int) $row['branch_id'] !== $scope) {
                throw new RuntimeException('Both accounts in a transfer must belong to your branch.');
            }
        }
    }

    protected function recentTransfers(): array
    {
        if (! $this->db->tableExists('transfers')) {
            return [];
        }

        $builder = $this->db->table('transfers')
            ->select('transfers.*, fa.account_no as from_no, fc.full_name as from_name, ta.account_no as to_no, tc.full_name as to_name, transactions.id as transaction_pk, transactions.tx_id')
            ->join('accounts fa', 'fa.id = transfers.from_account_id')
            ->join('customers fc', 'fc.id = fa.customer_id')
            ->join('accounts ta', 'ta.id = transfers.to_account_id')
            ->join('customers tc', 'tc.id = ta.customer_id')
            ->join('transactions', "transactions.reference_no = transfers.transfer_no AND transactions.type = 'TRANSFER'", 'left')
            ->orderBy('transfers.id', 'DESC')
            ->limit(50);

        if (session()->get('role') !== 'SUPER ADMIN') {
            $builder->where('fa.branch_id', (int) session()->get('branch_id'));
        }

        return $builder->get()->getResultArray();
    }
}
