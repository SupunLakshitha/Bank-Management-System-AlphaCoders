<?php

namespace App\Services;

use Config\Database;

class ReconciliationService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function reconcileAccounts(): array
    {
        if (!$this->db->tableExists('accounts')) {
            return [];
        }

        $builder = $this->db->table('accounts a')
            ->select('a.id, a.account_no, a.balance, a.customer_id, a.account_type_id');
        if ($this->db->tableExists('customers')) {
            $builder->select('c.full_name AS account_holder')->join('customers c', 'c.id = a.customer_id', 'left');
        }
        if ($this->db->tableExists('account_types')) {
            $builder->select('at.name AS account_type')->join('account_types at', 'at.id = a.account_type_id', 'left');
        }
        $accounts = $builder->orderBy('a.account_no')->get()->getResultArray();
        if (!$accounts) return [];

        $accountIds = array_map(static fn ($account) => (int) $account['id'], $accounts);
        $credits = array_fill_keys($accountIds, 0.0);
        $debits = array_fill_keys($accountIds, 0.0);

        if ($this->db->tableExists('deposits')) {
            $rows = $this->db->table('deposits')->select('account_id, SUM(amount) AS total')
                ->whereIn('account_id', $accountIds)->groupBy('account_id')->get()->getResultArray();
            foreach ($rows as $row) $credits[(int) $row['account_id']] += (float) $row['total'];
        }
        if ($this->db->tableExists('withdrawals')) {
            $rows = $this->db->table('withdrawals')->select('account_id, SUM(amount) AS total')
                ->whereIn('account_id', $accountIds)->groupBy('account_id')->get()->getResultArray();
            foreach ($rows as $row) $debits[(int) $row['account_id']] += (float) $row['total'];
        }
        if ($this->db->tableExists('transfers')) {
            $rows = $this->db->table('transfers')->select('from_account_id AS account_id, SUM(amount) AS total')
                ->whereIn('from_account_id', $accountIds)->where('status', 'POSTED')->groupBy('from_account_id')->get()->getResultArray();
            foreach ($rows as $row) $debits[(int) $row['account_id']] += (float) $row['total'];
            $rows = $this->db->table('transfers')->select('to_account_id AS account_id, SUM(amount) AS total')
                ->whereIn('to_account_id', $accountIds)->where('status', 'POSTED')->groupBy('to_account_id')->get()->getResultArray();
            foreach ($rows as $row) $credits[(int) $row['account_id']] += (float) $row['total'];
        }

        foreach ($accounts as &$account) {
            $id = (int) $account['id'];
            // This schema does not store an opening balance, so the computed value
            // represents recorded deposits and transfer credits less withdrawals and transfer debits.
            $account['total_credits'] = round($credits[$id], 2);
            $account['total_debits'] = round($debits[$id], 2);
            $account['computed_balance'] = round($credits[$id] - $debits[$id], 2);
            $account['stored_balance'] = (float) $account['balance'];
            $account['variance'] = round($account['computed_balance'] - $account['stored_balance'], 2);
            $account['reconciliation_status'] = abs($account['variance']) < 0.01 ? 'BALANCED' : 'DISCREPANCY';
            $account['account_holder'] = $account['account_holder'] ?? 'Unknown customer';
        }
        unset($account);

        return $accounts;
    }

    public function checkLedgerIntegrity(): array
    {
        $table = $this->db->tableExists('journal_lines') ? 'journal_lines'
            : ($this->db->tableExists('transaction_entries') ? 'transaction_entries' : null);
        if ($table === null) {
            return ['status' => 'IMBALANCED', 'total_debits' => 0.0, 'total_credits' => 0.0, 'variance' => 0.0];
        }

        $builder = $this->db->table($table . ' lines');
        if ($table === 'journal_lines' && $this->db->tableExists('journal_entries')) {
            $builder->join('journal_entries entries', 'entries.id = lines.journal_entry_id')
                ->where('entries.status', 'POSTED');
        } elseif ($table === 'transaction_entries' && $this->db->tableExists('transactions')) {
            $builder->join('transactions entries', 'entries.id = lines.transaction_id')
                ->where('entries.status', 'POSTED');
        }
        $totals = $builder->select('COALESCE(SUM(lines.debit), 0) AS total_debits, COALESCE(SUM(lines.credit), 0) AS total_credits')
            ->get()->getRowArray() ?? [];
        $debits = (float) ($totals['total_debits'] ?? 0);
        $credits = (float) ($totals['total_credits'] ?? 0);
        $variance = round($debits - $credits, 2);

        return [
            'status' => abs($variance) < 0.01 ? 'BALANCED' : 'IMBALANCED',
            'total_debits' => $debits,
            'total_credits' => $credits,
            'variance' => $variance,
        ];
    }
}
