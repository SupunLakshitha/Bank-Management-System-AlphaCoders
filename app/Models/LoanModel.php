<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table            = 'loans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'loan_account_no',
        'application_id',
        'customer_id',
        'loan_product_id',
        'branch_id',
        'approved_by_user_id',
        'principal_amount',
        'interest_rate',
        'tenure_months',
        'emi_amount',
        'total_payable',
        'total_paid',
        'outstanding_balance',
        'disbursed_at',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active loans with joined customer and product details
     */
    public function getActiveLoans(?int $branchId = null)
    {
        $this->select('loans.*, 
                              customers.full_name as customer_name, 
                              customers.customer_id as customer_code, 
                              customers.nic as customer_nic,
                              loan_products.name as product_name, 
                              loan_products.code as product_code, 
                              loan_products.interest_type,
                              branches.name as branch_name,
                              users.username as approver_username')
                    ->join('customers', 'customers.id = loans.customer_id', 'left')
                    ->join('loan_products', 'loan_products.id = loans.loan_product_id', 'left')
                    ->join('branches', 'branches.id = loans.branch_id', 'left')
                    ->join('users', 'users.id = loans.approved_by_user_id', 'left')
                    ->orderBy('loans.id', 'DESC');

        if ($branchId !== null) {
            $this->where('loans.branch_id', $branchId);
        }

        return $this->findAll();
    }

    /**
     * Get single loan details
     */
    public function getDetailedLoan($id)
    {
        return $this->select('loans.*, 
                              customers.full_name as customer_name, 
                              customers.customer_id as customer_code, 
                              customers.nic as customer_nic, 
                              customers.phone as customer_phone,
                              loan_products.name as product_name, 
                              loan_products.code as product_code, 
                              loan_products.interest_type,
                              branches.name as branch_name,
                              users.username as approver_username,
                              loan_applications.application_no')
                    ->join('customers', 'customers.id = loans.customer_id', 'left')
                    ->join('loan_products', 'loan_products.id = loans.loan_product_id', 'left')
                    ->join('branches', 'branches.id = loans.branch_id', 'left')
                    ->join('users', 'users.id = loans.approved_by_user_id', 'left')
                    ->join('loan_applications', 'loan_applications.id = loans.application_id', 'left')
                    ->where('loans.id', $id)
                    ->first();
    }

    /**
     * Generate unique Loan Account Number e.g. LN-202610-001
     */
    public static function generateLoanAccountNumber(): string
    {
        $prefix = 'LN-' . date('Ym') . '-';
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $random;
    }

    /**
     * Calculate monthly EMI and Total Payable
     */
    public static function calculateLoanFinancials(float $principal, float $annualRate, int $tenureMonths, string $interestType = 'compound'): array
    {
        if ($tenureMonths <= 0 || $principal <= 0) {
            return ['emi' => 0, 'total_payable' => 0];
        }

        if ($interestType === 'simple') {
            $totalInterest = $principal * ($annualRate / 100) * ($tenureMonths / 12);
            $totalPayable  = $principal + $totalInterest;
            $emi           = $totalPayable / $tenureMonths;
        } else {
            // Compound / Reducing Balance EMI Formula: P * r * (1+r)^n / ((1+r)^n - 1)
            $r = ($annualRate / 12) / 100;
            if ($r > 0) {
                $emi = ($principal * $r * pow(1 + $r, $tenureMonths)) / (pow(1 + $r, $tenureMonths) - 1);
                $totalPayable = $emi * $tenureMonths;
            } else {
                $emi = $principal / $tenureMonths;
                $totalPayable = $principal;
            }
        }

        return [
            'emi'           => round($emi, 2),
            'total_payable' => round($totalPayable, 2),
        ];
    }
}
