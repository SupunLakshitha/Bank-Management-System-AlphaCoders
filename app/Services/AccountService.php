<?php

namespace App\Services;

use App\Models\AccountModel;
use App\Models\AccountTypeModel;
use App\Models\CustomerModel;
use App\Models\DepositModel;
use App\Models\WithdrawalModel;
use Config\Database;

class AccountService
{
    protected $accountModel;
    protected $accountTypeModel;
    protected $customerModel;
    protected $depositModel;
    protected $withdrawalModel;
    protected $db;

    public function __construct()
    {
        $this->accountModel     = new AccountModel();
        $this->accountTypeModel = new AccountTypeModel();
        $this->customerModel    = new CustomerModel();
        $this->depositModel     = new DepositModel();
        $this->withdrawalModel  = new WithdrawalModel();
        $this->db               = Database::connect();
    }

    public function openAccount(array $data, ?int $tellerId = null): array
    {
        $customerId = (int)($data['customer_id'] ?? 0);
        $customer   = $this->customerModel->find($customerId);
        if (!$customer) {
            return ['ok' => false, 'error' => 'Selected customer not found.'];
        }

        $typeId = (int)($data['account_type_id'] ?? 0);
        $type   = $this->accountTypeModel->find($typeId);
        if (!$type || !(int)$type['is_active']) {
            return ['ok' => false, 'error' => 'Invalid or inactive account type selected.'];
        }

        $initialDeposit = isset($data['initial_deposit']) ? (float)$data['initial_deposit'] : 0.0;
        $minBalance     = (float)$type['min_balance'];

        if ($initialDeposit > 0 && $initialDeposit < $minBalance) {
            return [
                'ok'    => false,
                'error' => "Initial deposit must be at least Rs. " . number_format($minBalance, 2) . " for a " . $type['name'],
            ];
        }

        $this->db->transStart();

        $accountNo = $this->accountModel->generateAccountNumber();
        $interestRate = isset($data['interest_rate']) && $data['interest_rate'] !== '' 
            ? (float)$data['interest_rate'] 
            : (float)$type['interest_rate'];

        $branchId = !empty($data['branch_id']) ? (int)$data['branch_id'] : ($customer['branch_id'] ?? null);

        $accountData = [
            'account_no'      => $accountNo,
            'customer_id'     => $customerId,
            'account_type_id' => $typeId,
            'branch_id'       => $branchId,
            'balance'         => $initialDeposit,
            'interest_rate'   => $interestRate,
            'status'          => 'Active',
            'gl_account_id'   => $this->db->table('gl_accounts')->select('id')->where('code', 'GL-20100')->get()->getRowArray()['id'] ?? null,
        ];

        if (!$this->accountModel->insert($accountData)) {
            $this->db->transRollback();
            return ['ok' => false, 'errors' => $this->accountModel->errors()];
        }

        $accountId = $this->accountModel->getInsertID();

        // If initial deposit > 0, record deposit entry
        if ($initialDeposit > 0) {
            $depositId = $this->depositModel->generateDepositId();
            $this->depositModel->insert([
                'deposit_id'       => $depositId,
                'account_id'       => $accountId,
                'amount'           => $initialDeposit,
                'previous_balance' => 0.00,
                'new_balance'      => $initialDeposit,
                'deposit_method'   => $data['deposit_method'] ?? 'Cash',
                'teller_id'        => $tellerId,
                'reference_number' => 'INITIAL-DEP-' . $accountNo,
                'remarks'          => 'Opening balance deposit',
            ]);

            // Sync with transactions table if exists
            if ($this->db->tableExists('transactions')) {
                $this->db->table('transactions')->insert([
                    'tx_id'        => 'TX' . date('ymdHis') . rand(1000, 9999),
                    'account_id'   => $accountId,
                    'type'         => 'DEPOSIT',
                    'amount'       => $initialDeposit,
                    'tx_date'      => date('Y-m-d'),
                    'description'  => 'Initial deposit upon account opening',
                    'reference_no' => 'INITIAL-DEP-' . $accountNo,
                    'branch_id'    => $branchId,
                    'created_by'   => $tellerId,
                    'status'       => 'POSTED',
                    'created_at'   => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            return ['ok' => false, 'error' => 'Database transaction failed during account opening.'];
        }

        return [
            'ok'         => true,
            'account_id' => $accountId,
            'account_no' => $accountNo,
            'account'    => $this->accountModel->getAccountWithDetails($accountId),
        ];
    }

    public function updateStatus(int $accountId, string $status): array
    {
        $map = [
            'active'    => 'Active',
            'inactive'  => 'Frozen',
            'suspended' => 'Frozen',
            'frozen'    => 'Frozen',
            'closed'    => 'Closed',
        ];
        $key = strtolower($status);
        if (! isset($map[$key])) {
            return ['ok' => false, 'error' => 'Invalid account status.'];
        }
        $status = $map[$key];



        

        $account = $this->accountModel->find($accountId);
        if (!$account) {
            return ['ok' => false, 'error' => 'Account not found.'];
        }

        $this->accountModel->update($accountId, ['status' => $status]);

        return [
            'ok'      => true,
            'status'  => $status,
            'message' => "Account status changed to {$status}.",
        ];
    }

    public function getAccountStatement(int $accountId): ?array
    {
        $account = $this->accountModel->getAccountWithDetails($accountId);
        if (!$account) {
            return null;
        }

        $deposits    = $this->depositModel->getDepositsByAccount($accountId, 50);
        $withdrawals = $this->withdrawalModel->getWithdrawalsByAccount($accountId, 50);

        // Combine and sort by date descending
        $history = [];
        foreach ($deposits as $d) {
            $history[] = [
                'type'             => 'DEPOSIT',
                'id'               => $d['id'],
                'ref'              => $d['deposit_id'],
                'amount'           => (float)$d['amount'],
                'previous_balance' => (float)$d['previous_balance'],
                'new_balance'      => (float)$d['new_balance'],
                'method'           => $d['deposit_method'],
                'teller'           => $d['teller_username'] ?? 'System',
                'remarks'          => $d['remarks'],
                'date'             => $d['created_at'],
            ];
        }
        foreach ($withdrawals as $w) {
            $history[] = [
                'type'             => 'WITHDRAWAL',
                'id'               => $w['id'],
                'ref'              => $w['withdrawal_id'],
                'amount'           => (float)$w['amount'],
                'previous_balance' => (float)$w['previous_balance'],
                'new_balance'      => (float)$w['new_balance'],
                'method'           => $w['withdrawal_method'],
                'teller'           => $w['teller_username'] ?? 'System',
                'remarks'          => $w['remarks'],
                'date'             => $w['created_at'],
            ];
        }

        usort($history, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return [
            'account' => $account,
            'history' => $history,
        ];
    }
}
