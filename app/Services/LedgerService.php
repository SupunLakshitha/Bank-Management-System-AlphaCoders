<?php

namespace App\Services;

use Config\Database;
use RuntimeException;

class LedgerService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Core chart used by disbursements, transfers, and the ledger screens.
     *
     * @return list<array{code:string,name:string,account_type:string,normal_balance:string}>
     */
    public function coreChart(): array
    {
        return [
            ['code' => 'GL-10100', 'name' => 'Cash on Hand', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
            ['code' => 'GL-10400', 'name' => 'Loans Receivable', 'account_type' => 'ASSET', 'normal_balance' => 'DEBIT'],
            ['code' => 'GL-20100', 'name' => 'Customer Savings Deposits', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
            ['code' => 'GL-20200', 'name' => 'Customer Current Deposits', 'account_type' => 'LIABILITY', 'normal_balance' => 'CREDIT'],
            ['code' => 'GL-30100', 'name' => 'Share Capital', 'account_type' => 'EQUITY', 'normal_balance' => 'CREDIT'],
            ['code' => 'GL-40100', 'name' => 'Interest Income', 'account_type' => 'INCOME', 'normal_balance' => 'CREDIT'],
            ['code' => 'GL-40200', 'name' => 'Fee Income', 'account_type' => 'INCOME', 'normal_balance' => 'CREDIT'],
            ['code' => 'GL-50100', 'name' => 'Operating Expenses', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
            ['code' => 'GL-50200', 'name' => 'Interest Expense on Deposits', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
            ['code' => 'GL-50300', 'name' => 'Ledger Adjustments', 'account_type' => 'EXPENSE', 'normal_balance' => 'DEBIT'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function typeLabels(): array
    {
        return [
            'DEPOSIT'            => 'Deposit',
            'WITHDRAWAL'         => 'Withdrawal',
            'TRANSFER'           => 'Transfer',
            'LOAN_DISBURSEMENT'  => 'Loan disbursement',
            'LOAN_REPAYMENT'     => 'Loan repayment',
            'INTEREST_CREDIT'    => 'Interest credit',
            'FEE'                => 'Fees',
            'ADJUSTMENT'         => 'Adjustments',
        ];
    }

    public function ensureCoreChart(): void
    {
        if (! $this->db->tableExists('gl_accounts')) {
            throw new RuntimeException('Ledger tables are not migrated yet.');
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->coreChart() as $account) {
            $existing = $this->db->table('gl_accounts')->where('code', $account['code'])->get()->getRowArray();
            if ($existing) {
                continue;
            }

            $this->db->table('gl_accounts')->insert($account + [
                'is_system'  => 1,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Post a balanced journal. Customer-account lines move the deposit balance:
     * a credit increases it and a debit decreases it.
     *
     * @param array<string, mixed>              $entry
     * @param list<array<string, mixed>>        $lines
     * @return array{id:int,entry_no:string,amount:float}
     */
    public function postEntry(array $entry, array $lines, bool $manageTransaction = true): array
    {
        $this->ensureCoreChart();
        $this->backfillFromJournals();

        if ($lines === []) {
            throw new RuntimeException('A journal entry needs at least one line.');
        }

        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        $normalized  = [];

        foreach ($lines as $line) {
            $debit  = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit < 0 || $credit < 0 || ($debit > 0 && $credit > 0) || ($debit === 0.0 && $credit === 0.0)) {
                throw new RuntimeException('Each journal line must be either a debit or a credit.');
            }

            $gl = $this->glByCode((string) ($line['gl_code'] ?? ''));
            if (! $gl) {
                throw new RuntimeException('Unknown ledger account ' . ($line['gl_code'] ?? '') . '.');
            }

            $totalDebit  += $debit;
            $totalCredit += $credit;
            $normalized[] = [
                'gl_account_id' => (int) $gl['id'],
                'account_id'    => ! empty($line['account_id']) ? (int) $line['account_id'] : null,
                'debit'         => $debit,
                'credit'        => $credit,
                'narration'     => (string) ($line['narration'] ?? ($entry['description'] ?? '')),
            ];
        }

        $totalDebit  = round($totalDebit, 2);
        $totalCredit = round($totalCredit, 2);
        if (abs($totalDebit - $totalCredit) > 0.009) {
            throw new RuntimeException('Journal is out of balance. Debits must equal credits.');
        }

        $now     = date('Y-m-d H:i:s');
        $entryNo = (string) ($entry['entry_no'] ?? ('JE-' . date('YmdHis') . '-' . random_int(100, 999)));
        $type    = $this->normalizeType((string) ($entry['type'] ?? $entry['reference_type'] ?? 'ADJUSTMENT'));
        $txDate  = $this->normalizeDate((string) ($entry['tx_date'] ?? substr((string) ($entry['entry_date'] ?? $now), 0, 10)));

        if ($type === 'TRANSFER') {
            $this->assertBalancedTransferLines($normalized);
        }

        if ($manageTransaction) {
            $this->db->transBegin();
        }

        try {
            $locked = $this->lockAccountsInOrder($normalized);
            $deltas = $this->balanceDeltas($normalized);
            foreach ($deltas as $accountId => $delta) {
                $newBalance = round((float) $locked[$accountId]['balance'] + $delta, 2);
                if ($newBalance < -0.001) {
                    throw new RuntimeException('Insufficient balance on account ' . $locked[$accountId]['account_no'] . '.');
                }
            }

            $txId = (string) ($entry['tx_id'] ?? $this->nextTxId());
            $counterAccountId = $entry['counter_account_id'] ?? $this->counterAccountId($type, $normalized);
            $branchId = $entry['branch_id'] ?? null;
            if (! $branchId) {
                $primaryAccountId = $entry['account_id'] ?? $this->primaryAccountId($normalized, $type);
                if ($primaryAccountId) {
                    $primaryAccount = $this->db->table('accounts')->select('branch_id')->where('id', $primaryAccountId)->get()->getRowArray();
                    $branchId = $primaryAccount['branch_id'] ?? null;
                }
            }
            if (! $branchId) {
                throw new RuntimeException('A transaction must be posted to a branch.');
            }

            $this->db->table('journal_entries')->insert([
                'entry_no'       => $entryNo,
                'entry_date'     => $entry['entry_date'] ?? $now,
                'reference_type' => $entry['reference_type'] ?? $type,
                'reference_no'   => $entry['reference_no'] ?? null,
                'description'    => $entry['description'] ?? 'Journal entry',
                'branch_id'      => $branchId,
                'created_by'     => $entry['created_by'] ?? null,
                'status'         => 'POSTED',
                'created_at'     => $now,
            ]);
            $entryId = (int) $this->db->insertID();

            $this->db->table('transactions')->insert([
                'tx_id'               => $txId,
                'account_id'          => $entry['account_id'] ?? $this->primaryAccountId($normalized, $type),
                'counter_account_id'  => $counterAccountId,
                'type'                => $type,
                'amount'              => number_format($totalDebit, 2, '.', ''),
                'tx_date'             => $txDate,
                'description'         => $entry['description'] ?? 'Transaction',
                'reference_no'        => $entry['reference_no'] ?? null,
                'branch_id'           => $branchId,
                'created_by'          => $entry['created_by'] ?? null,
                'status'              => 'POSTED',
                'created_at'          => $now,
            ]);
            $transactionId = $this->requireInsertedTransaction($txId);

            foreach ($normalized as $line) {
                $journalLine = $line;
                $journalLine['journal_entry_id'] = $entryId;
                $journalLine['debit']            = number_format($line['debit'], 2, '.', '');
                $journalLine['credit']           = number_format($line['credit'], 2, '.', '');
                $this->db->table('journal_lines')->insert($journalLine);

                $this->db->table('transaction_entries')->insert([
                    'transaction_id' => $transactionId,
                    'gl_account_id'  => $line['gl_account_id'],
                    'account_id'     => $line['account_id'],
                    'debit'          => number_format($line['debit'], 2, '.', ''),
                    'credit'         => number_format($line['credit'], 2, '.', ''),
                    'narration'      => $line['narration'],
                ]);
            }

            foreach ($deltas as $accountId => $delta) {
                $newBalance = round((float) $locked[$accountId]['balance'] + $delta, 2);
                $this->db->table('accounts')->where('id', $accountId)->update([
                    'balance'    => number_format($newBalance, 2, '.', ''),
                    'updated_at' => $now,
                ]);
            }

            if ($manageTransaction) {
                $this->db->transCommit();
            }
        } catch (\Throwable $e) {
            if ($manageTransaction) {
                $this->db->transRollback();
            }
            throw $e;
        }

        return [
            'id'             => $entryId,
            'entry_no'       => $entryNo,
            'transaction_id' => $transactionId,
            'tx_id'          => $txId,
            'amount'         => $totalDebit,
        ];
    }

    /**
     * Move money between two customer accounts and post the matching journal.
     *
     * @return array{transfer_no:string,journal_entry_id:int,entry_no:string,amount:float}
     */
    public function transfer(int $fromId, int $toId, float $amount, string $narration, ?int $userId, ?int $branchId = null): array
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new RuntimeException('Transfer amount must be greater than zero.');
        }
        if ($fromId === $toId) {
            throw new RuntimeException('Choose two different accounts.');
        }

        $from = $this->db->table('accounts')->where('id', $fromId)->get()->getRowArray();
        $to   = $this->db->table('accounts')->where('id', $toId)->get()->getRowArray();
        if (! $from || ! $to) {
            throw new RuntimeException('One of the accounts could not be found.');
        }
        if ($from['status'] !== 'Active' || $to['status'] !== 'Active') {
            throw new RuntimeException('Both accounts must be active.');
        }

        $fromGl = $this->glCodeById((int) $from['gl_account_id']);
        $toGl   = $this->glCodeById((int) $to['gl_account_id']);
        $transferNo = 'TRF-' . date('YmdHis') . '-' . random_int(100, 999);
        $movement = $from['account_no'] . ' → Rs. ' . number_format($amount, 2) . ' → ' . $to['account_no'];
        $description = trim($narration) !== '' ? trim($narration) : $movement;

        $lines = [
            [
                'gl_code'    => $fromGl,
                'account_id' => $fromId,
                'debit'      => $amount,
                'credit'     => 0,
                'narration'  => 'Transfer out ' . $from['account_no'],
            ],
            [
                'gl_code'    => $toGl,
                'account_id' => $toId,
                'debit'      => 0,
                'credit'     => $amount,
                'narration'  => 'Transfer in ' . $to['account_no'],
            ],
        ];
        $this->assertBalancedTransferLines([
            ['account_id' => $fromId, 'debit' => $amount, 'credit' => 0],
            ['account_id' => $toId, 'debit' => 0, 'credit' => $amount],
        ]);

        $this->db->transBegin();
        try {
            $posted = $this->postEntry([
                'type'               => 'TRANSFER',
                'account_id'         => $fromId,
                'counter_account_id' => $toId,
                'reference_type'     => 'TRANSFER',
                'reference_no'       => $transferNo,
                'description'        => $description,
                'branch_id'          => $branchId ?? ($from['branch_id'] ?? null),
                'created_by'         => $userId,
            ], $lines, false);

            if (abs((float) $posted['amount'] - $amount) > 0.009) {
                throw new RuntimeException('The transfer amount did not match the ledger entries.');
            }

            $this->db->table('transfers')->insert([
                'transfer_no'      => $transferNo,
                'from_account_id'  => $fromId,
                'to_account_id'    => $toId,
                'amount'           => number_format($amount, 2, '.', ''),
                'narration'        => $description,
                'status'           => 'POSTED',
                'journal_entry_id' => $posted['id'],
                'created_by'       => $userId,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return [
            'transfer_no'      => $transferNo,
            'journal_entry_id' => $posted['id'],
            'transaction_id'   => $posted['transaction_id'],
            'tx_id'            => $posted['tx_id'],
            'entry_no'         => $posted['tx_id'],
            'amount'           => $amount,
        ];
    }

    /**
     * Post a customer-account transaction. The deposit balance moves only from the entries.
     *
     * @return array{id:int,entry_no:string,transaction_id:int,tx_id:string,amount:float}
     */
    public function postCustomerTransaction(
        string $type,
        int $accountId,
        float $amount,
        string $narration,
        ?int $userId,
        ?string $txDate = null,
        ?string $adjustmentSide = null,
        ?int $loanId = null
    ): array {
        $type   = $this->normalizeType($type);
        $amount = round($amount, 2);
        $txDate = $this->normalizeDate($txDate ?: date('Y-m-d'));

        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }
        if ($txDate > date('Y-m-d')) {
            throw new RuntimeException('Transaction date cannot be in the future.');
        }

        $account = $this->db->table('accounts')->where('id', $accountId)->get()->getRowArray();
        if (! $account || $account['status'] !== 'Active') {
            throw new RuntimeException('Choose an active deposit account.');
        }

        $glCode      = $this->glCodeById((int) $account['gl_account_id']);
        $description = trim($narration) !== '' ? trim($narration) : ($this->typeLabels()[$type] ?? $type);
        $lines       = [];
        $loan        = null;

        if ($type === 'DEPOSIT') {
            $lines = [
                ['gl_code' => 'GL-10100', 'debit' => $amount, 'credit' => 0, 'narration' => 'Cash received'],
                ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => 0, 'credit' => $amount, 'narration' => 'Deposit to ' . $account['account_no']],
            ];
        } elseif ($type === 'WITHDRAWAL') {
            $lines = [
                ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => $amount, 'credit' => 0, 'narration' => 'Withdrawal from ' . $account['account_no']],
                ['gl_code' => 'GL-10100', 'debit' => 0, 'credit' => $amount, 'narration' => 'Cash paid out'],
            ];
        } elseif ($type === 'INTEREST_CREDIT') {
            $lines = [
                ['gl_code' => 'GL-50200', 'debit' => $amount, 'credit' => 0, 'narration' => 'Deposit interest expense'],
                ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => 0, 'credit' => $amount, 'narration' => 'Interest credited to ' . $account['account_no']],
            ];
        } elseif ($type === 'FEE') {
            $lines = [
                ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => $amount, 'credit' => 0, 'narration' => 'Fee charged to ' . $account['account_no']],
                ['gl_code' => 'GL-40200', 'debit' => 0, 'credit' => $amount, 'narration' => 'Fee income'],
            ];
        } elseif ($type === 'ADJUSTMENT') {
            $side = $adjustmentSide === 'debit' ? 'debit' : 'credit';
            if ($side === 'credit') {
                $lines = [
                    ['gl_code' => 'GL-50300', 'debit' => $amount, 'credit' => 0, 'narration' => 'Adjustment offset'],
                    ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => 0, 'credit' => $amount, 'narration' => 'Credit adjustment ' . $account['account_no']],
                ];
            } else {
                $lines = [
                    ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => $amount, 'credit' => 0, 'narration' => 'Debit adjustment ' . $account['account_no']],
                    ['gl_code' => 'GL-50300', 'debit' => 0, 'credit' => $amount, 'narration' => 'Adjustment offset'],
                ];
            }
        } elseif ($type === 'LOAN_REPAYMENT') {
            $loan = $this->db->table('loans')->where('id', $loanId)->get()->getRowArray();
            if (! $loan || $loan['status'] !== 'Active') {
                throw new RuntimeException('Choose an active loan.');
            }
            if ((int) $loan['customer_id'] !== (int) $account['customer_id']) {
                throw new RuntimeException('The deposit account does not belong to the borrower.');
            }
            if ($amount - (float) $loan['outstanding_balance'] > 0.009) {
                throw new RuntimeException('Repayment is larger than the outstanding loan balance.');
            }
            $lines = [
                ['gl_code' => $glCode, 'account_id' => $accountId, 'debit' => $amount, 'credit' => 0, 'narration' => 'Repayment from ' . $account['account_no']],
                ['gl_code' => 'GL-10400', 'debit' => 0, 'credit' => $amount, 'narration' => 'Loans receivable ' . $loan['loan_account_no']],
            ];
            $description = trim($narration) !== '' ? trim($narration) : 'Loan repayment ' . $loan['loan_account_no'];
        } else {
            throw new RuntimeException('That transaction type cannot be posted from this screen.');
        }

        $this->db->transBegin();
        try {
            $posted = $this->postEntry([
                'type'           => $type,
                'account_id'     => $accountId,
                'tx_date'        => $txDate,
                'reference_type' => $type,
                'reference_no'   => $loan['loan_account_no'] ?? null,
                'description'    => $description,
                'branch_id'      => $account['branch_id'] ?? null,
                'created_by'     => $userId,
            ], $lines, false);

            if ($loan) {
                $outstanding = round((float) $loan['outstanding_balance'] - $amount, 2);
                if ($outstanding < 0) {
                    $outstanding = 0;
                }
                $this->db->table('loans')->where('id', $loan['id'])->update([
                    'total_paid'          => number_format((float) $loan['total_paid'] + $amount, 2, '.', ''),
                    'outstanding_balance' => number_format($outstanding, 2, '.', ''),
                    'status'              => $outstanding <= 0.009 ? 'Completed' : 'Active',
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return $posted;
    }

    /**
     * Loan disbursement: debit loans receivable, credit the customer's savings account.
     */
    public function disburseLoanToSavings(int $customerId, float $amount, string $loanAccountNo, ?int $branchId = null): array
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new RuntimeException('Disbursement amount must be greater than zero.');
        }

        $account = $this->findOrCreateSavings($customerId, $branchId);
        $posted  = $this->postEntry([
            'type'           => 'LOAN_DISBURSEMENT',
            'account_id'     => (int) $account['id'],
            'reference_type' => 'LOAN_DISBURSEMENT',
            'reference_no'   => $loanAccountNo,
            'description'    => "Loan disbursement for facility {$loanAccountNo}",
            'branch_id'      => $branchId,
            'created_by'     => session()->get('user_id'),
        ], [
            [
                'gl_code'   => 'GL-10400',
                'debit'     => $amount,
                'credit'    => 0,
                'narration' => 'Loans receivable ' . $loanAccountNo,
            ],
            [
                'gl_code'    => 'GL-20100',
                'account_id' => (int) $account['id'],
                'debit'      => 0,
                'credit'     => $amount,
                'narration'  => 'Credited to ' . $account['account_no'],
            ],
        ]);

        return [
            'transaction_id'     => $posted['tx_id'],
            'journal_entry_id'   => $posted['id'],
            'loan_account_no'    => $loanAccountNo,
            'savings_account_no' => $account['account_no'],
            'customer_id'        => $customerId,
            'branch_id'          => $branchId,
            'amount'             => $amount,
            'debit_account'      => 'GL-10400 (Loans Receivable)',
            'credit_account'     => 'GL-20100 (Customer Savings Deposit)',
            'description'        => "Loan disbursement for facility {$loanAccountNo}",
            'status'             => 'SUCCESS',
            'created_at'         => date('Y-m-d H:i:s'),
        ];
    }

    public function findOrCreateSavings(int $customerId, ?int $branchId = null): array
    {
        $this->ensureCoreChart();

        $account = $this->db->table('accounts')
            ->where('customer_id', $customerId)
            ->where('account_type', 'SAVINGS')
            ->where('status', 'Active')
            ->get()
            ->getRowArray();

        if ($account) {
            return $account;
        }

        if (! $branchId) {
            $customer = $this->db->table('customers')->select('branch_id')->where('id', $customerId)->get()->getRowArray();
            $branchId = isset($customer['branch_id']) ? (int) $customer['branch_id'] : null;
        }
        if (! $branchId) {
            throw new RuntimeException('A savings account must be opened at the customer\'s branch.');
        }

        $gl = $this->glByCode('GL-20100');
        $now = date('Y-m-d H:i:s');
        $this->db->table('accounts')->insert([
            'account_no'    => $this->nextAccountNo('SAVINGS'),
            'customer_id'   => $customerId,
            'branch_id'     => $branchId,
            'gl_account_id' => (int) $gl['id'],
            'account_type'  => 'SAVINGS',
            'currency'      => 'LKR',
            'balance'       => '0.00',
            'status'        => 'Active',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        return $this->db->table('accounts')->where('id', $this->db->insertID())->get()->getRowArray();
    }

    public function glByCode(string $code): ?array
    {
        if ($code === '') {
            return null;
        }

        return $this->db->table('gl_accounts')->where('code', $code)->get()->getRowArray() ?: null;
    }

    protected function glCodeById(int $id): string
    {
        $gl = $this->db->table('gl_accounts')->where('id', $id)->get()->getRowArray();
        if (! $gl) {
            throw new RuntimeException('Deposit account is not linked to a ledger account.');
        }

        return $gl['code'];
    }

    protected function lockAccount(int $id): array
    {
        $account = $this->db->query('SELECT * FROM accounts WHERE id = ? FOR UPDATE', [$id])->getRowArray();
        if (! $account) {
            throw new RuntimeException('Deposit account not found.');
        }
        if ($account['status'] !== 'Active') {
            throw new RuntimeException('Account ' . $account['account_no'] . ' is not active.');
        }

        return $account;
    }

    public function nextAccountNo(string $type): string
    {
        $prefix = $type === 'CURRENT' ? 'CA-' : 'SA-';
        $row = $this->db->query(
            'SELECT account_no FROM accounts WHERE account_no LIKE ? ORDER BY id DESC LIMIT 1',
            [$prefix . '%']
        )->getRowArray();

        $next = 100001;
        if ($row && preg_match('/(\d+)$/', $row['account_no'], $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $prefix . $next;
    }

    /**
     * Copy journals that were posted before the transaction register existed.
     * Balances are not changed again.
     */
    public function backfillFromJournals(): void
    {
        if (! $this->db->tableExists('transactions') || ! $this->db->tableExists('journal_entries')) {
            return;
        }
        if ((int) $this->db->table('transactions')->countAllResults() > 0) {
            return;
        }

        $journals = $this->db->table('journal_entries')->orderBy('id', 'ASC')->get()->getResultArray();
        if ($journals === []) {
            return;
        }

        $ownTransaction = $this->db->transDepth === 0;
        if ($ownTransaction) {
            $this->db->transBegin();
        }

        try {
            foreach ($journals as $journal) {
                $lines = $this->db->table('journal_lines')
                    ->where('journal_entry_id', $journal['id'])
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();

                if ($journal['reference_type'] === 'OPENING') {
                    foreach ($lines as $line) {
                        if (empty($line['account_id']) || (float) $line['credit'] <= 0) {
                            continue;
                        }
                        $amount = round((float) $line['credit'], 2);
                        $this->insertHistoricalTransaction(
                            'ADJUSTMENT',
                            (int) $line['account_id'],
                            $amount,
                            substr((string) $journal['entry_date'], 0, 10),
                            'Opening balance ' . $this->accountNo((int) $line['account_id']),
                            $journal['reference_no'] ?? null,
                            $journal['branch_id'] ?? null,
                            $journal['created_by'] ?? null,
                            $journal['created_at'] ?? $journal['entry_date'],
                            [
                                [
                                    'gl_account_id' => $this->cashGlId($lines),
                                    'account_id'    => null,
                                    'debit'         => $amount,
                                    'credit'        => 0,
                                    'narration'     => 'Cash funding for opening balance',
                                ],
                                [
                                    'gl_account_id' => (int) $line['gl_account_id'],
                                    'account_id'    => (int) $line['account_id'],
                                    'debit'         => 0,
                                    'credit'        => $amount,
                                    'narration'     => $line['narration'],
                                ],
                            ]
                        );
                    }
                    continue;
                }

                $type = $this->normalizeType((string) $journal['reference_type']);
                $normalized = [];
                foreach ($lines as $line) {
                    $normalized[] = [
                        'gl_account_id' => (int) $line['gl_account_id'],
                        'account_id'    => $line['account_id'] ? (int) $line['account_id'] : null,
                        'debit'         => (float) $line['debit'],
                        'credit'        => (float) $line['credit'],
                        'narration'     => $line['narration'],
                    ];
                }

                $this->insertHistoricalTransaction(
                    $type,
                    $this->primaryAccountId($normalized, $type),
                    round(array_sum(array_column($normalized, 'debit')), 2),
                    substr((string) $journal['entry_date'], 0, 10),
                    (string) $journal['description'],
                    $journal['reference_no'] ?? null,
                    $journal['branch_id'] ?? null,
                    $journal['created_by'] ?? null,
                    $journal['created_at'] ?? $journal['entry_date'],
                    $normalized
                );
            }

            if ($ownTransaction) {
                $this->db->transCommit();
            }
        } catch (\Throwable $e) {
            if ($ownTransaction) {
                $this->db->transRollback();
            }
            throw $e;
        }
    }

    protected function insertHistoricalTransaction(
        string $type,
        ?int $accountId,
        float $amount,
        string $txDate,
        string $description,
        ?string $referenceNo,
        $branchId,
        $createdBy,
        ?string $createdAt,
        array $lines
    ): void {
        $txId = $this->nextTxId();
        $this->db->table('transactions')->insert([
            'tx_id'              => $txId,
            'account_id'         => $accountId,
            'counter_account_id' => $this->counterAccountId($type, $lines),
            'type'               => $type,
            'amount'             => number_format($amount, 2, '.', ''),
            'tx_date'            => $txDate,
            'description'        => $description,
            'reference_no'       => $referenceNo,
            'branch_id'          => $branchId,
            'created_by'         => $createdBy,
            'status'             => 'POSTED',
            'created_at'         => $createdAt,
        ]);
        $transactionId = $this->requireInsertedTransaction($txId);

        foreach ($lines as $line) {
            $this->db->table('transaction_entries')->insert([
                'transaction_id' => $transactionId,
                'gl_account_id'  => $line['gl_account_id'],
                'account_id'     => $line['account_id'],
                'debit'          => number_format((float) $line['debit'], 2, '.', ''),
                'credit'         => number_format((float) $line['credit'], 2, '.', ''),
                'narration'      => $line['narration'],
            ]);
        }
    }

    protected function cashGlId(array $lines): int
    {
        foreach ($lines as $line) {
            if (empty($line['account_id']) && (float) $line['debit'] > 0) {
                return (int) $line['gl_account_id'];
            }
        }

        $gl = $this->glByCode('GL-10100');
        if (! $gl) {
            throw new RuntimeException('Cash ledger account is missing.');
        }

        return (int) $gl['id'];
    }

    protected function accountNo(int $accountId): string
    {
        $account = $this->db->table('accounts')->select('account_no')->where('id', $accountId)->get()->getRowArray();

        return $account['account_no'] ?? ('Account ' . $accountId);
    }

    protected function nextTxId(): string
    {
        $row = $this->db->query(
            "SELECT tx_id FROM transactions WHERE tx_id REGEXP '^TX[0-9]+$' ORDER BY CAST(SUBSTRING(tx_id, 3) AS UNSIGNED) DESC LIMIT 1"
        )->getRowArray();
        $next = 1;
        if ($row && preg_match('/^TX(\d+)$/', (string) $row['tx_id'], $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'TX' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * A failed insert leaves the previous statement's id in place when database
     * exceptions are off, which would attach these lines to the wrong transaction.
     */
    protected function requireInsertedTransaction(string $txId): int
    {
        $transactionId = (int) $this->db->insertID();
        $saved = $transactionId > 0
            ? $this->db->table('transactions')->select('tx_id')->where('id', $transactionId)->get()->getRowArray()
            : null;

        if (! $saved || ($saved['tx_id'] ?? '') !== $txId) {
            $error = $this->db->error();
            throw new RuntimeException($error['message'] ?: 'The transaction could not be recorded in the ledger.');
        }

        return $transactionId;
    }

    protected function normalizeType(string $type): string
    {
        $type = strtoupper(str_replace([' ', '-'], '_', trim($type)));
        if (in_array($type, ['OPENING', 'JOURNAL', ''], true)) {
            $type = 'ADJUSTMENT';
        }
        if (! isset($this->typeLabels()[$type])) {
            return 'ADJUSTMENT';
        }

        return $type;
    }

    protected function normalizeDate(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new RuntimeException('Enter a valid transaction date.');
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    protected function primaryAccountId(array $lines, string $type): ?int
    {
        if ($type === 'TRANSFER') {
            foreach ($lines as $line) {
                if (! empty($line['account_id']) && (float) $line['debit'] > 0) {
                    return (int) $line['account_id'];
                }
            }
        }

        $ids = [];
        foreach ($lines as $line) {
            if (! empty($line['account_id'])) {
                $ids[] = (int) $line['account_id'];
            }
        }
        $ids = array_values(array_unique($ids));

        return count($ids) === 1 ? $ids[0] : null;
    }

    /**
     * A transfer is one pair: debit account A and credit account B for the same amount.
     *
     * @param list<array<string, mixed>> $lines
     */
    protected function assertBalancedTransferLines(array $lines): void
    {
        $debitAccount  = null;
        $creditAccount = null;
        $debit         = 0.0;
        $credit        = 0.0;

        foreach ($lines as $line) {
            if (empty($line['account_id'])) {
                throw new RuntimeException('A transfer must name both customer accounts in the ledger.');
            }
            if ((float) $line['debit'] > 0 && (float) $line['credit'] > 0) {
                throw new RuntimeException('Each transfer line must be either a debit or a credit.');
            }
            if ((float) $line['debit'] > 0) {
                if ($debitAccount !== null) {
                    throw new RuntimeException('A transfer can debit only one account.');
                }
                $debitAccount = (int) $line['account_id'];
                $debit        = round((float) $line['debit'], 2);
            }
            if ((float) $line['credit'] > 0) {
                if ($creditAccount !== null) {
                    throw new RuntimeException('A transfer can credit only one account.');
                }
                $creditAccount = (int) $line['account_id'];
                $credit        = round((float) $line['credit'], 2);
            }
        }

        if ($debitAccount === null || $creditAccount === null || $debitAccount === $creditAccount) {
            throw new RuntimeException('A transfer must debit one account and credit a different account.');
        }
        if (abs($debit - $credit) > 0.009) {
            throw new RuntimeException('Both sides of the transfer must be the same amount.');
        }
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @return array<int, array<string, mixed>>
     */
    protected function lockAccountsInOrder(array $lines): array
    {
        $ids = [];
        foreach ($lines as $line) {
            if (! empty($line['account_id'])) {
                $ids[] = (int) $line['account_id'];
            }
        }
        $ids = array_values(array_unique($ids));
        sort($ids);

        $locked = [];
        foreach ($ids as $id) {
            $locked[$id] = $this->lockAccount($id);
        }

        return $locked;
    }

    /**
     * @param list<array<string, mixed>> $lines
     * @return array<int, float>
     */
    protected function balanceDeltas(array $lines): array
    {
        $deltas = [];
        foreach ($lines as $line) {
            if (empty($line['account_id'])) {
                continue;
            }
            $accountId = (int) $line['account_id'];
            $deltas[$accountId] = round(($deltas[$accountId] ?? 0) + (float) $line['credit'] - (float) $line['debit'], 2);
        }

        return $deltas;
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    protected function counterAccountId(string $type, array $lines): ?int
    {
        if ($type !== 'TRANSFER') {
            return null;
        }

        foreach ($lines as $line) {
            if (! empty($line['account_id']) && (float) $line['credit'] > 0) {
                return (int) $line['account_id'];
            }
        }

        return null;
    }
}
