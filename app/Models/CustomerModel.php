<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'branch_id',
        'customer_id',
        'full_name',
        'nic',
        'dob',
        'phone',
        'email',
        'address',
        'occupation',
        'kyc_status',
        'branch_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'customer_id' => 'required|min_length[3]|max_length[20]',
        'full_name'   => 'required|min_length[3]|max_length[255]',
        'nic'         => 'permit_empty|max_length[20]',
        'phone'       => 'permit_empty|max_length[20]',
        'email'       => 'permit_empty|valid_email|max_length[150]',
        'kyc_status'  => 'in_list[Pending,Verified,Rejected]',
    ];

    public function generateCustomerId(): string
    {
        $last = $this->orderBy('id', 'DESC')->first();
        if (!$last) {
            return 'C001';
        }

        // Try extracting numeric portion
        if (preg_match('/C(\d+)/', $last['customer_id'], $matches)) {
            $nextNum = (int)$matches[1] + 1;
            return sprintf('C%03d', $nextNum);
        }

        return 'C' . str_pad((string)($last['id'] + 1), 3, '0', STR_PAD_LEFT);
    }

    public function getWithBranch(int $id): ?array
    {
        return $this->select('customers.*, branches.name as branch_name, branches.code as branch_code')
            ->join('branches', 'branches.id = customers.branch_id', 'left')
            ->where('customers.id', $id)
            ->first();
    }

    public function searchCustomers(?string $query = null, ?string $kycStatus = null, ?int $branchId = null): array
    {
        $builder = $this->select('customers.*, branches.name as branch_name, branches.code as branch_code')
            ->join('branches', 'branches.id = customers.branch_id', 'left');

        if (!empty($query)) {
            $builder->groupStart()
                ->like('customers.full_name', $query)
                ->orLike('customers.customer_id', $query)
                ->orLike('customers.nic', $query)
                ->orLike('customers.phone', $query)
                ->orLike('customers.email', $query)
                ->groupEnd();
        }

        if (!empty($kycStatus)) {
            $builder->where('customers.kyc_status', $kycStatus);
        }

        if (!empty($branchId)) {
            $builder->where('customers.branch_id', $branchId);
        }

        return $builder->orderBy('customers.id', 'DESC')->findAll();
    }
}
