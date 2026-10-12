<?php

namespace App\Services;

use App\Models\AccountModel;
use App\Models\DepositModel;
use App\Models\WithdrawalModel;
use Config\Database;

class DepositWithdrawalService
{
    protected $accountModel;
    protected $depositModel;
    protected $withdrawalModel;
    protected $db;

    public function __construct()
    {
        $this->accountModel    = new AccountModel();
        $this->depositModel    = new DepositModel();
        $this->withdrawalModel = new WithdrawalModel();
        $this->db              = Database::connect();
    }

    /**
     * Process a cash/cheque/online deposit into an account.
     * Atomic transaction with FOR UPDATE row locking.
     */
    public function processDeposit(
        $accountIdentifier,
        float $amount,
        string $method = 'Cash',
        ?int $tellerId = null,
        ?string $referenceNo = null,
        ?string $remarks = null
    ): array {
        if ($amount <= 0) {
            return ['ok' => false, 'error' => 'Deposit amount must be greater than zero.'];
        }

        $this->db->transStart();

        // 1. Lock account row for update
        $accountQuery = is_numeric($accountIdentifier)
            ? $this->db->query('SELECT * FROM accounts WHERE id = ? OR account_no = ? FOR UPDATE', [(int)$accountIdentifier, (string)$accountIdentifier])
            : $this->db->query('SELECT * FROM accounts WHERE account_no = ? FOR UPDATE', [(string)$accountIdentifier]);

        $account = $accountQuery->getRowArray();

        if (!$account) {
            $this->db->transRollback();
            return ['ok' => false, 'error' => 'Bank account not found.'];
        }

        if ($account['status'] !== 'active') {
            $this->db->transRollback();
            return ['ok' => false, 'error' => "Account is {$account['status']}. Transactions are not permitted."];
        }

        $previousBalance = (float)$account['balance'];
        $newBalance      = round($previousBalance + $amount, 2);
        $timestamp       = date('Y-m-d H:i:s');
        $depositId       = $this->depositModel->generateDepositId();

        // 2. Update account balance
        $this->db->table('accounts')->where('id', $account['id'])->update([
            'balance'    => $newBalance,
            'updated_at' => $timestamp,
        ]);

        // 3. Insert deposit record
        $this->db->table('deposits')->insert([
            'deposit_id'       => $depositId,
            'account_id'       => $account['id'],
            'amount'           => $amount,
            'previous_balance' => $previousBalance,
            'new_balance'      => $newBalance,
            'deposit_method'   => $method,
            'teller_id'        => $tellerId,
            'reference_number' => $referenceNo,
            'remarks'          => $remarks,
            'created_at'       => $timestamp,
            'updated_at'       => $timestamp,
        ]);
        $insertedId = $this->db->insertID();

        // 4. Optional sync with central transactions ledger if table exists
        if ($this->db->tableExists('transactions')) {
            $this->db->table('transactions')->insert([
                'tx_id'       => 'TX-DEP-' . date('YmdHis') . '-' . rand(100, 999),
                'account_no'  => $account['account_no'],
                'type'        => 'DEPOSIT',
                'amount'      => $amount,
                'description' => $remarks ?: "Deposit via {$method}",
                'created_at'  => $timestamp,
            ]);
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['ok' => false, 'error' => 'Deposit transaction failed to commit.'];
        }

        return [
            'ok'               => true,
            'id'               => $insertedId,
            'deposit_id'       => $depositId,
            'account_no'       => $account['account_no'],
            'amount'           => $amount,
            'previous_balance' => $previousBalance,
            'new_balance'      => $newBalance,
            'deposit'          => $this->depositModel->getDepositWithDetails($insertedId),
        ];
    }

    /**
     * Process a withdrawal from an account.
     * Strict validation: Available Balance >= Withdrawal Amount
     * Atomic transaction with FOR UPDATE row locking.
     */
    public function processWithdrawal(
        $accountIdentifier,
        float $amount,
        string $method = 'Cash',
        ?int $tellerId = null,
        ?string $referenceNo = null,
        ?string $remarks = null
    ): array {
        if ($amount <= 0) {
            return ['ok' => false, 'error' => 'Withdrawal amount must be greater than zero.'];
        }

        $this->db->transStart();

        // 1. Lock account row for update
        $accountQuery = is_numeric($accountIdentifier)
            ? $this->db->query('SELECT a.*, at.min_balance FROM accounts a LEFT JOIN account_types at ON at.id = a.account_type_id WHERE a.id = ? OR a.account_no = ? FOR UPDATE', [(int)$accountIdentifier, (string)$accountIdentifier])
            : $this->db->query('SELECT a.*, at.min_balance FROM accounts a LEFT JOIN account_types at ON at.id = a.account_type_id WHERE a.account_no = ? FOR UPDATE', [(string)$accountIdentifier]);

        $account = $accountQuery->getRowArray();

        if (!$account) {
            $this->db->transRollback();
            return ['ok' => false, 'error' => 'Bank account not found.'];
        }

        if ($account['status'] !== 'active') {
            $this->db->transRollback();
            return ['ok' => false, 'error' => "Account is {$account['status']}. Withdrawals are not permitted."];
        }

        $previousBalance = (float)$account['balance'];

        // Strict balance check (Available Balance >= Withdrawal Amount)
        if ($previousBalance < $amount) {
            $this->db->transRollback();
            return [
                'ok'    => false,
                'error' => "Insufficient funds. Available Balance: Rs. " . number_format($previousBalance, 2) . ", Requested: Rs. " . number_format($amount, 2),
            ];
        }

        // Check minimum balance requirement of account type
        $minBalance = isset($account['min_balance']) ? (float)$account['min_balance'] : 0.0;
        if (($previousBalance - $amount) < $minBalance) {
            $this->db->transRollback();
            return [
                'ok'    => false,
                'error' => "Withdrawal would violate minimum balance requirement of Rs. " . number_format($minBalance, 2) . ". Max allowed withdrawal: Rs. " . number_format(max(0, $previousBalance - $minBalance), 2),
            ];
        }

        $newBalance = round($previousBalance - $amount, 2);
        $timestamp  = date('Y-m-d H:i:s');
        $wthId      = $this->withdrawalModel->generateWithdrawalId();

        // 2. Update account balance
        $this->db->table('accounts')->where('id', $account['id'])->update([
            'balance'    => $newBalance,
            'updated_at' => $timestamp,
        ]);

        // 3. Insert withdrawal record
        $this->db->table('withdrawals')->insert([
            'withdrawal_id'    => $wthId,
            'account_id'       => $account['id'],
            'amount'           => $amount,
            'previous_balance' => $previousBalance,
            'new_balance'      => $newBalance,
            'withdrawal_method'=> $method,
            'teller_id'        => $tellerId,
            'reference_number' => $referenceNo,
            'remarks'          => $remarks,
            'created_at'       => $timestamp,
            'updated_at'       => $timestamp,
        ]);
        $insertedId = $this->db->insertID();

        // 4. Optional sync with central transactions ledger if table exists
        if ($this->db->tableExists('transactions')) {
            $this->db->table('transactions')->insert([
                'tx_id'       => 'TX-WTH-' . date('YmdHis') . '-' . rand(100, 999),
                'account_no'  => $account['account_no'],
                'type'        => 'WITHDRAWAL',
                'amount'      => $amount,
                'description' => $remarks ?: "Withdrawal via {$method}",
                'created_at'  => $timestamp,
            ]);
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['ok' => false, 'error' => 'Withdrawal transaction failed to commit.'];
        }

        return [
            'ok'               => true,
            'id'               => $insertedId,
            'withdrawal_id'    => $wthId,
            'account_no'       => $account['account_no'],
            'amount'           => $amount,
            'previous_balance' => $previousBalance,
            'new_balance'      => $newBalance,
            'withdrawal'       => $this->withdrawalModel->getWithdrawalWithDetails($insertedId),
        ];
    }
}
