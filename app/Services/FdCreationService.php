<?php

namespace App\Services;

use App\Libraries\FdInterestCalculator;
use Config\Database;

class FdCreationService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * @return array{ok:bool, status:int, error?:string, data?:array}
     */
    public function open(int $customerId, int $productId, int $accountId, float $amount): array
    {
        if (! $this->db->tableExists('accounts')) {
            return $this->fail(503, 'Accounts module not available yet.');
        }

        $product = $this->db->table('fd_products')->where('id', $productId)->get()->getRowArray();
        if (! $product) {
            return $this->fail(404, 'FD product not found.');
        }
        if (! (int) $product['is_active']) {
            return $this->fail(422, 'This FD product is not active.');
        }
        if ($amount < (float) $product['min_amount']) {
            return $this->fail(422, 'Amount is below the minimum of ' . $product['min_amount'] . '.');
        }

        $customer = $this->db->table('customers')->where('id', $customerId)->get()->getRowArray();
        if (! $customer) {
            return $this->fail(404, 'Customer not found.');
        }

        $this->db->transStart();

        // Lock the source account row so the balance check and deduction are atomic
        $account = $this->db->query('SELECT * FROM accounts WHERE id = ? FOR UPDATE', [$accountId])->getRowArray();

        if (! $account || (int) $account['customer_id'] !== $customerId) {
            $this->db->transComplete();
            return $this->fail(404, 'Source account not found for this customer.');
        }
        if (strtolower($account['status'] ?? 'active') !== 'active') {
            $this->db->transComplete();
            return $this->fail(422, 'Source account is not active.');
        }
        if ((float) $account['balance'] < $amount) {
            $this->db->transComplete();
            return $this->fail(422, 'Insufficient balance in the source account.');
        }

        $calc    = new FdInterestCalculator();
        $start   = date('Y-m-d');
        $preview = $calc->previewMaturity(
            $amount,
            (float) $product['interest_rate'],
            (int) $product['tenure_months'],
            $start,
            $product['interest_type'],
            FdInterestCalculator::ACTUAL_365,
            (int) $product['compounding_per_year']
        );

        $now = date('Y-m-d H:i:s');

        
        $this->db->table('fixed_deposits')->insert([
            'fd_number'            => 'TMP',
            'customer_id'          => $customerId,
            'branch_id'            => $account['branch_id'],
            'fd_product_id'        => $productId,
            'account_id'           => $accountId,
            'principal'            => $amount,
            'interest_rate'        => $product['interest_rate'],
            'interest_type'        => $product['interest_type'],
            'day_count'            => FdInterestCalculator::ACTUAL_365,
            'compounding_per_year' => $product['compounding_per_year'],
            'tenure_months'        => $product['tenure_months'],
            'start_date'           => $start,
            'maturity_date'        => $preview['maturity_date'],
            'accrued_interest'     => 0,
            'status'               => 'active',
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);
        $fdId     = $this->db->insertID();
        $fdNumber = sprintf('FD-%s-%06d', date('Y'), $fdId);
        $this->db->table('fixed_deposits')->where('id', $fdId)->update(['fd_number' => $fdNumber]);

        $this->db->table('accounts')->where('id', $accountId)->update([
            'balance'    => round((float) $account['balance'] - $amount, 2),
            'updated_at' => $now,
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return $this->fail(500, 'Could not create the FD. Nothing was changed.');
        }

        return ['ok' => true, 'status' => 201, 'data' => $this->certificate($fdId)];
    }

    public function certificate(int $fdId): ?array
    {
        $fd = $this->db->table('fixed_deposits fd')
            ->select('fd.*, c.id AS cust_id, p.name AS product_name')
            ->join('customers c', 'c.id = fd.customer_id', 'left')
            ->join('fd_products p', 'p.id = fd.fd_product_id', 'left')
            ->where('fd.id', $fdId)
            ->get()->getRowArray();

        if (! $fd) {
            return null;
        }

        $calc    = new FdInterestCalculator();
        $preview = $calc->previewMaturity(
            (float) $fd['principal'],
            (float) $fd['interest_rate'],
            (int) $fd['tenure_months'],
            $fd['start_date'],
            $fd['interest_type'],
            $fd['day_count'],
            (int) $fd['compounding_per_year']
        );

        return [
            'fd_number'       => $fd['fd_number'],
            'customer_id'     => (int) $fd['customer_id'],
            'source_account'  => (int) $fd['account_id'],
            'product'         => $fd['product_name'],
            'principal'       => (float) $fd['principal'],
            'interest_rate'   => (float) $fd['interest_rate'],
            'interest_type'   => $fd['interest_type'],
            'tenure_months'   => (int) $fd['tenure_months'],
            'start_date'      => $fd['start_date'],
            'maturity_date'   => $fd['maturity_date'],
            'interest_at_maturity' => $preview['interest_earned'],
            'maturity_amount' => $preview['maturity_amount'],
            'status'          => $fd['status'],
            'issued_at'       => $fd['created_at'],
        ];
    }

    private function fail(int $status, string $message): array
    {
        return ['ok' => false, 'status' => $status, 'error' => $message];
    }
}