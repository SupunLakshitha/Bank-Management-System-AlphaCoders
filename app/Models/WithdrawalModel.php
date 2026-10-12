<?php

namespace App\Models;

use CodeIgniter\Model;

class WithdrawalModel extends Model
{
    protected $table            = 'withdrawals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'withdrawal_id',
        'account_id',
        'amount',
        'previous_balance',
        'new_balance',
        'withdrawal_method',
        'teller_id',
        'reference_number',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateWithdrawalId(): string
    {
        return 'WTH-' . date('Ymd') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function getWithdrawalWithDetails(int $id): ?array
    {
        return $this->select('withdrawals.*, accounts.account_no, customers.customer_id as cust_code, customers.full_name as customer_name, customers.nic as customer_nic, customers.phone as customer_phone, users.username as teller_username, branches.name as branch_name')
            ->join('accounts', 'accounts.id = withdrawals.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('users', 'users.id = withdrawals.teller_id', 'left')
            ->join('branches', 'branches.id = accounts.branch_id', 'left')
            ->where('withdrawals.id', $id)
            ->first();
    }

    public function getWithdrawalsByAccount(int $accountId, int $limit = 50): array
    {
        return $this->select('withdrawals.*, users.username as teller_username')
            ->join('users', 'users.id = withdrawals.teller_id', 'left')
            ->where('withdrawals.account_id', $accountId)
            ->orderBy('withdrawals.id', 'DESC')
            ->findAll($limit);
    }

    public function searchWithdrawals(?string $query = null, ?string $method = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        $builder = $this->select('withdrawals.*, accounts.account_no, customers.customer_id as cust_code, customers.full_name as customer_name, users.username as teller_username')
            ->join('accounts', 'accounts.id = withdrawals.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('users', 'users.id = withdrawals.teller_id', 'left');

        if (!empty($query)) {
            $builder->groupStart()
                ->like('withdrawals.withdrawal_id', $query)
                ->orLike('withdrawals.reference_number', $query)
                ->orLike('accounts.account_no', $query)
                ->orLike('customers.full_name', $query)
                ->groupEnd();
        }

        if (!empty($method)) {
            $builder->where('withdrawals.withdrawal_method', $method);
        }

        if (!empty($fromDate)) {
            $builder->where('DATE(withdrawals.created_at) >=', $fromDate);
        }

        if (!empty($toDate)) {
            $builder->where('DATE(withdrawals.created_at) <=', $toDate);
        }

        return $builder->orderBy('withdrawals.id', 'DESC')->findAll();
    }
}
