<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table            = 'accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'account_no',
        'customer_id',
        'account_type_id',
        'branch_id',
        'balance',
        'interest_rate',
    protected $protectFields    = true;
    protected $allowedFields    = [
        'account_no',
        'customer_id',
        'branch_id',
        'gl_account_id',
        'account_type',
        'currency',
        'balance',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'account_no'      => 'required|min_length[8]|max_length[20]|is_unique[accounts.account_no,id,{id}]',
        'customer_id'     => 'required|is_not_unique[customers.id]',
        'account_type_id' => 'required|is_not_unique[account_types.id]',
        'balance'         => 'permit_empty|decimal',
        'status'          => 'in_list[active,inactive,suspended,closed]',
    ];

    public function generateAccountNumber(): string
    {
        do {
            // Standard 10-digit bank account number starting with 10
            $accountNo = '10' . str_pad((string) mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
            $exists = $this->where('account_no', $accountNo)->first();
        } while ($exists);

        return $accountNo;
    }

    public function getAccountWithDetails($idOrAccountNo): ?array
    {
        $builder = $this->select('accounts.*, customers.customer_id as cust_code, customers.full_name as customer_name, customers.nic as customer_nic, customers.phone as customer_phone, customers.email as customer_email, account_types.name as account_type_name, account_types.code as account_type_code, account_types.min_balance, branches.name as branch_name, branches.code as branch_code')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('account_types', 'account_types.id = accounts.account_type_id', 'left')
            ->join('branches', 'branches.id = accounts.branch_id', 'left');

        if (is_numeric($idOrAccountNo)) {
            $builder->groupStart()
                ->where('accounts.id', (int)$idOrAccountNo)
                ->orWhere('accounts.account_no', (string)$idOrAccountNo)
                ->groupEnd();
        } else {
            $builder->where('accounts.account_no', (string)$idOrAccountNo);
        }

        return $builder->first();
    }

    public function getAccountsByCustomer(int $customerId): array
    {
        return $this->select('accounts.*, account_types.name as account_type_name, account_types.code as account_type_code, branches.name as branch_name')
            ->join('account_types', 'account_types.id = accounts.account_type_id', 'left')
            ->join('branches', 'branches.id = accounts.branch_id', 'left')
            ->where('accounts.customer_id', $customerId)
            ->orderBy('accounts.id', 'DESC')
            ->findAll();
    }

    public function searchAccounts(?string $query = null, ?string $status = null, ?int $typeId = null, ?int $branchId = null): array
    {
        $builder = $this->select('accounts.*, customers.customer_id as cust_code, customers.full_name as customer_name, customers.nic as customer_nic, customers.phone as customer_phone, account_types.name as account_type_name, account_types.code as account_type_code, branches.name as branch_name')
            ->join('customers', 'customers.id = accounts.customer_id', 'left')
            ->join('account_types', 'account_types.id = accounts.account_type_id', 'left')
            ->join('branches', 'branches.id = accounts.branch_id', 'left');

        if (!empty($query)) {
            $builder->groupStart()
                ->like('accounts.account_no', $query)
                ->orLike('customers.full_name', $query)
                ->orLike('customers.customer_id', $query)
                ->orLike('customers.nic', $query)
                ->groupEnd();
        }

        if (!empty($status)) {
            $builder->where('accounts.status', $status);
        }

        if (!empty($typeId)) {
            $builder->where('accounts.account_type_id', $typeId);
        }

        if (!empty($branchId)) {
            $builder->where('accounts.branch_id', $branchId);
        }

        return $builder->orderBy('accounts.id', 'DESC')->findAll();
    public function findSavingsForCustomer(int $customerId): ?array
    {
        return $this->where('customer_id', $customerId)
            ->where('account_type', 'SAVINGS')
            ->first();
    }

    public function ensureSavingsAccount(int $customerId, int $branchId, ?string $accountNo = null): array
    {
        $existing = $this->findSavingsForCustomer($customerId);
        if ($existing) {
            if ((int) $existing['branch_id'] !== $branchId) {
                $this->update($existing['id'], ['branch_id' => $branchId]);
                $existing['branch_id'] = $branchId;
            }

            return $existing;
        }

        $gl = $this->db->table('gl_accounts')->where('code', 'GL-20100')->get()->getRowArray();

        $this->insert([
            'account_no'    => $accountNo ?: ('SA-' . str_pad((string) $customerId, 6, '0', STR_PAD_LEFT)),
            'customer_id'   => $customerId,
            'branch_id'     => $branchId,
            'gl_account_id' => $gl['id'] ?? null,
            'account_type'  => 'SAVINGS',
            'currency'      => 'LKR',
            'balance'       => 0,
            'status'        => 'Active',
        ]);

        return $this->find($this->getInsertID());
    }
}
