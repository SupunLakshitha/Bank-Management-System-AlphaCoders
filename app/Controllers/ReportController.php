<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    protected $db;

    public function __construct()
    {
        if (!session()->get('isLoggedIn')) {
            redirect()->to(base_url('login'))->send();
            exit;
        }

        $this->db = \Config\Database::connect();
    }

    private function branches(): array
    {
        return $this->db->tableExists('branches')
            ? $this->db->table('branches')->orderBy('name')->get()->getResultArray()
            : [];
    }

    private function dateFilters(): array
    {
        $today = date('Y-m-d');
        $start = $this->request->getGet('start_date') ?: $today;
        $end = $this->request->getGet('end_date') ?: $today;
        return [$start, $end, (int) $this->request->getGet('branch_id')];
    }

    public function index()
    {
        return view('reports/index', ['title' => 'Reports Hub']);
    }

    public function dailyTransactions()
    {
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $type = $this->request->getGet('type');
        $branchId = (int) $this->request->getGet('branch_id');
        $rows = [];

        if ($this->db->tableExists('transactions')) {
            $builder = $this->db->table('transactions t')->select('t.*');
            if ($this->db->tableExists('accounts')) $builder->select('a.account_no')->join('accounts a', 'a.id = t.account_id', 'left');
            if ($this->db->tableExists('branches')) $builder->select('b.name AS branch_name')->join('branches b', 'b.id = t.branch_id', 'left');
            if ($this->db->fieldExists('tx_date', 'transactions')) $builder->where('t.tx_date', $date);
            if (!empty($type)) $builder->where('t.type', $type);
            if ($branchId > 0) $builder->where('t.branch_id', $branchId);
            $rows = $builder->orderBy('t.id', 'DESC')->get()->getResultArray();
        }

        return view('reports/transactions', [
            'transactions' => $rows, 'branches' => $this->branches(),
            'filters' => ['date' => $date, 'type' => $type, 'branch_id' => $branchId],
            'totalCount' => count($rows), 'totalAmount' => array_sum(array_column($rows, 'amount')),
        ]);
    }

    private function operationReport(string $table, string $view, string $idColumn): string
    {
        [$start, $end, $branchId] = $this->dateFilters();
        $rows = [];
        if ($this->db->tableExists($table)) {
            $builder = $this->db->table($table . ' op')->select('op.*');
            if ($this->db->tableExists('accounts')) $builder->select('a.account_no, a.branch_id')->join('accounts a', 'a.id = op.account_id', 'left');
            if ($this->db->tableExists('branches')) $builder->select('b.name AS branch_name')->join('branches b', 'b.id = a.branch_id', 'left');
            $builder->where('DATE(op.created_at) >=', $start)->where('DATE(op.created_at) <=', $end);
            if ($branchId > 0) $builder->where('a.branch_id', $branchId);
            $rows = $builder->orderBy('op.created_at', 'DESC')->get()->getResultArray();
        }
        return view('reports/' . $view, [
            'rows' => $rows, 'branches' => $this->branches(),
            'filters' => ['start_date' => $start, 'end_date' => $end, 'branch_id' => $branchId],
            'totalCount' => count($rows), 'totalAmount' => array_sum(array_column($rows, 'amount')),
            'recordIdColumn' => $idColumn,
        ]);
    }

    public function deposits() { return $this->operationReport('deposits', 'deposits', 'deposit_id'); }
    public function withdrawals() { return $this->operationReport('withdrawals', 'withdrawals', 'withdrawal_id'); }

    public function loans()
    {
        $status = $this->request->getGet('status');
        $branchId = (int) $this->request->getGet('branch_id');
        $rows = [];
        if ($this->db->tableExists('loans')) {
            $builder = $this->db->table('loans l')->select('l.*');
            if ($this->db->tableExists('customers')) $builder->select('c.first_name, c.last_name')->join('customers c', 'c.id = l.customer_id', 'left');
            if ($this->db->tableExists('branches')) $builder->select('b.name AS branch_name')->join('branches b', 'b.id = l.branch_id', 'left');
            if ($branchId > 0) $builder->where('l.branch_id', $branchId);
            if ($status === 'overdue') $builder->whereIn('l.status', ['Defaulted', 'Overdue']);
            elseif ($status === 'active') $builder->where('l.status', 'Active');
            elseif ($status === 'approved') $builder->where('l.status', 'Approved');
            $rows = $builder->orderBy('l.created_at', 'DESC')->get()->getResultArray();
        }
        return view('reports/loans', [
            'loans' => $rows, 'branches' => $this->branches(),
            'filters' => ['status' => $status, 'branch_id' => $branchId],
            'totalCount' => count($rows),
            'totalAmount' => array_sum(array_column($rows, 'outstanding_balance')),
        ]);
    }

    public function tellerSummary()
    {
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $rows = [];
        foreach (['deposits' => 'deposit_total', 'withdrawals' => 'withdrawal_total'] as $table => $alias) {
            if (!$this->db->tableExists($table)) continue;
            $rows[$table] = $this->db->table($table)
                ->select('teller_id, COUNT(*) AS transaction_count, SUM(amount) AS ' . $alias)
                ->where('DATE(created_at)', $date)->groupBy('teller_id')->get()->getResultArray();
        }
        $summary = [];
        foreach ($rows as $operation => $results) {
            foreach ($results as $row) {
                $id = $row['teller_id'] ?? 'unassigned';
                $summary[$id] ??= ['teller_id' => $id, 'transaction_count' => 0, 'deposit_total' => 0, 'withdrawal_total' => 0];
                $summary[$id]['transaction_count'] += (int) $row['transaction_count'];
                if ($operation === 'deposits') $summary[$id]['deposit_total'] = (float) $row['deposit_total'];
                else $summary[$id]['withdrawal_total'] = (float) $row['withdrawal_total'];
            }
        }
        $users = $this->db->tableExists('users') ? $this->db->table('users')->get()->getResultArray() : [];
        $userNames = [];
        foreach ($users as $user) $userNames[$user['id']] = $user['username'] ?? ($user['name'] ?? 'User ' . $user['id']);
        foreach ($summary as &$row) $row['teller_name'] = $userNames[$row['teller_id']] ?? 'Unassigned';
        unset($row);
        return view('reports/teller', [
            'summary' => array_values($summary), 'date' => $date,
            'totalCount' => array_sum(array_column($summary, 'transaction_count')),
            'totalAmount' => array_sum(array_column($summary, 'deposit_total')) + array_sum(array_column($summary, 'withdrawal_total')),
        ]);
    }
}
