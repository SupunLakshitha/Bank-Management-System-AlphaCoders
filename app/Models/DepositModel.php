<?php

namespace App\Models;

use CodeIgniter\Model;

class DepositModel extends Model
{
    protected $table            = 'deposits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'deposit_id',
        'account_id',
        'amount',
        'previous_balance',
        'new_balance',
        'deposit_method',
        'teller_id',
        'reference_number',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateDepositId(): string
    {
        return 'DEP-' . date('Ymd') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function getDepositWithDetails(int $id): ?array
    {
        return $this->select('deposits.*, accounts.account_no, customers.customer_id as cust_code, customers.full_name as customer_name, customers.nic as customer_nic, customers.phone as customer_phone, users.username as teller_username, branches.name as branch_name')
            ->join('accounts', 'accounts.id = deposits.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('users', 'users.id = deposits.teller_id', 'left')
            ->join('branches', 'branches.id = accounts.branch_id', 'left')
            ->where('deposits.id', $id)
            ->first();
    }

    public function getDepositsByAccount(int $accountId, int $limit = 50): array
    {
        return $this->select('deposits.*, users.username as teller_username')
            ->join('users', 'users.id = deposits.teller_id', 'left')
            ->where('deposits.account_id', $accountId)
            ->orderBy('deposits.id', 'DESC')
            ->findAll($limit);
    }

    public function searchDeposits(?string $query = null, ?string $method = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        $builder = $this->select('deposits.*, accounts.account_no, customers.customer_id as cust_code, customers.full_name as customer_name, users.username as teller_username')
            ->join('accounts', 'accounts.id = deposits.account_id', 'left')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('users', 'users.id = deposits.teller_id', 'left');

        if (!empty($query)) {
            $builder->groupStart()
                ->like('deposits.deposit_id', $query)
                ->orLike('deposits.reference_number', $query)
                ->orLike('accounts.account_no', $query)
                ->orLike('customers.full_name', $query)
                ->groupEnd();
        }

        if (!empty($method)) {
            $builder->where('deposits.deposit_method', $method);
        }

        if (!empty($fromDate)) {
            $builder->where('DATE(deposits.created_at) >=', $fromDate);
        }

        if (!empty($toDate)) {
            $builder->where('DATE(deposits.created_at) <=', $toDate);
        }

        return $builder->orderBy('deposits.id', 'DESC')->findAll();
    }
}
