<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\AccountModel;
use App\Models\UserModel;
use Config\Database;

class CustomerService
{
    protected $customerModel;
    protected $accountModel;
    protected $db;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->accountModel  = new AccountModel();
        $this->db            = Database::connect();
    }

    public function createCustomer(array $data, bool $createLoginAccount = false, ?string $password = null): array
    {
        $this->db->transStart();

        // 1. Generate Customer ID if not provided
        if (empty($data['customer_id'])) {
            $data['customer_id'] = $this->customerModel->generateCustomerId();
        }

        // 2. Optionally create User login account
        $userId = null;
        if ($createLoginAccount && !empty($data['username'])) {
            $userModel = new UserModel();
            $customerRole = $this->db->table('roles')->where('name', 'Customer')->get()->getRowArray();
            $roleId = $customerRole ? $customerRole['id'] : 7;

            $userModel->insert([
                'username'      => $data['username'],
                'password_hash' => password_hash($password ?? 'Customer@123', PASSWORD_DEFAULT),
                'role_id'       => $roleId,
                'branch_id'     => $data['branch_id'] ?? null,
            ]);
            $userId = $userModel->getInsertID();
        }

        $customerInsert = [
            'user_id'     => $userId,
            'customer_id' => $data['customer_id'],
            'full_name'   => trim($data['full_name']),
            'nic'         => !empty($data['nic']) ? trim($data['nic']) : null,
            'dob'         => !empty($data['dob']) ? $data['dob'] : null,
            'phone'       => !empty($data['phone']) ? trim($data['phone']) : null,
            'email'       => !empty($data['email']) ? trim($data['email']) : null,
            'address'     => !empty($data['address']) ? trim($data['address']) : null,
            'occupation'  => !empty($data['occupation']) ? trim($data['occupation']) : null,
            'kyc_status'  => in_array($data['kyc_status'] ?? '', ['Verified', 'Rejected']) ? $data['kyc_status'] : 'Pending',
            'branch_id'   => !empty($data['branch_id']) ? (int)$data['branch_id'] : null,
        ];

        if (!$this->customerModel->insert($customerInsert)) {
            $this->db->transRollback();
            return [
                'ok'     => false,
                'errors' => $this->customerModel->errors(),
            ];
        }

        $customerId = $this->customerModel->getInsertID();
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['ok' => false, 'error' => 'Database transaction failed during customer creation.'];
        }

        return [
            'ok'          => true,
            'id'          => $customerId,
            'customer_id' => $data['customer_id'],
            'customer'    => $this->customerModel->getWithBranch($customerId),
        ];
    }

    public function updateCustomer(int $id, array $data): array
    {
        $existing = $this->customerModel->find($id);
        if (!$existing) {
            return ['ok' => false, 'error' => 'Customer not found.'];
        }

        $customerUpdate = [
            'full_name'   => trim($data['full_name'] ?? $existing['full_name']),
            'nic'         => isset($data['nic']) ? trim($data['nic']) : $existing['nic'],
            'dob'         => !empty($data['dob']) ? $data['dob'] : $existing['dob'],
            'phone'       => isset($data['phone']) ? trim($data['phone']) : $existing['phone'],
            'email'       => isset($data['email']) ? trim($data['email']) : $existing['email'],
            'address'     => isset($data['address']) ? trim($data['address']) : $existing['address'],
            'occupation'  => isset($data['occupation']) ? trim($data['occupation']) : $existing['occupation'],
            'branch_id'   => isset($data['branch_id']) ? (!empty($data['branch_id']) ? (int)$data['branch_id'] : null) : $existing['branch_id'],
        ];

        if (isset($data['kyc_status']) && in_array($data['kyc_status'], ['Pending', 'Verified', 'Rejected'])) {
            $customerUpdate['kyc_status'] = $data['kyc_status'];
        }

        if (!$this->customerModel->update($id, $customerUpdate)) {
            return [
                'ok'     => false,
                'errors' => $this->customerModel->errors(),
            ];
        }

        return [
            'ok'       => true,
            'customer' => $this->customerModel->getWithBranch($id),
        ];
    }

    public function updateKycStatus(int $id, string $status): array
    {
        if (!in_array($status, ['Pending', 'Verified', 'Rejected'])) {
            return ['ok' => false, 'error' => 'Invalid KYC status value.'];
        }

        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return ['ok' => false, 'error' => 'Customer not found.'];
        }

        $this->customerModel->update($id, ['kyc_status' => $status]);

        return [
            'ok'         => true,
            'status'     => $status,
            'message'    => "Customer KYC status updated to {$status}.",
        ];
    }

    public function getCustomerProfile(int $id): ?array
    {
        $customer = $this->customerModel->getWithBranch($id);
        if (!$customer) {
            return null;
        }

        $accounts = $this->accountModel->getAccountsByCustomer($id);
        $totalBalance = 0.0;
        foreach ($accounts as $acc) {
            if ($acc['status'] === 'active') {
                $totalBalance += (float)$acc['balance'];
            }
        }

        return [
            'customer'      => $customer,
            'accounts'      => $accounts,
            'total_balance' => $totalBalance,
            'account_count' => count($accounts),
        ];
    }
}
