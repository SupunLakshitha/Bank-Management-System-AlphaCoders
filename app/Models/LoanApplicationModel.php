<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanApplicationModel extends Model
{
    protected $table            = 'loan_applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'application_no',
        'customer_id',
        'loan_product_id',
        'branch_id',
        'created_by_user_id',
        'amount_requested',
        'tenure_months',
        'proposed_interest_rate',
        'purpose',
        'monthly_income',
        'collateral_type',
        'collateral_value',
        'collateral_description',
        'guarantor_name',
        'guarantor_nic',
        'guarantor_phone',
        'guarantor_relationship',
        'guarantor_address',
        'kyc_doc_path',
        'income_doc_path',
        'status',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'customer_id'            => 'required|integer',
        'loan_product_id'        => 'required|integer',
        'amount_requested'       => 'required|numeric|greater_than[0]',
        'tenure_months'          => 'required|integer|greater_than[0]',
        'proposed_interest_rate' => 'required|numeric|greater_than[0]',
    ];

    /**
     * Get detailed application with joined Customer, Product, Branch, and Officer info
     */
    public function getDetailedApplication($id)
    {
        return $this->select('loan_applications.*, 
                              customers.full_name as customer_name, 
                              customers.customer_id as customer_code, 
                              customers.nic as customer_nic, 
                              customers.phone as customer_phone, 
                              customers.occupation as customer_occupation,
                              loan_products.name as product_name, 
                              loan_products.code as product_code, 
                              loan_products.interest_type,
                              branches.name as branch_name,
                              users.username as officer_username')
                    ->join('customers', 'customers.id = loan_applications.customer_id', 'left')
                    ->join('loan_products', 'loan_products.id = loan_applications.loan_product_id', 'left')
                    ->join('branches', 'branches.id = loan_applications.branch_id', 'left')
                    ->join('users', 'users.id = loan_applications.created_by_user_id', 'left')
                    ->where('loan_applications.id', $id)
                    ->first();
    }

    /**
     * Get all applications with joined details
     */
    public function getApplicationsList($status = null, ?int $branchId = null)
    {
        $builder = $this->select('loan_applications.*, 
                                  customers.full_name as customer_name, 
                                  customers.customer_id as customer_code, 
                                  customers.nic as customer_nic, 
                                  loan_products.name as product_name, 
                                  loan_products.code as product_code,
                                  branches.name as branch_name,
                                  users.username as officer_username')
                        ->join('customers', 'customers.id = loan_applications.customer_id', 'left')
                        ->join('loan_products', 'loan_products.id = loan_applications.loan_product_id', 'left')
                        ->join('branches', 'branches.id = loan_applications.branch_id', 'left')
                        ->join('users', 'users.id = loan_applications.created_by_user_id', 'left')
                        ->orderBy('loan_applications.id', 'DESC');

        if (!empty($status)) {
            $builder->where('loan_applications.status', $status);
        }

        if ($branchId !== null) {
            $builder->where('loan_applications.branch_id', $branchId);
        }

        return $builder->findAll();
    }

    /**
     * Generate unique application number e.g. APP-202610-0001
     */
    public static function generateApplicationNumber(): string
    {
        $prefix = 'APP-' . date('Ym') . '-';
        $random = strtoupper(substr(uniqid(), -4));
        return $prefix . $random;
    }
}
