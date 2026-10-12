<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LoanApplicationModel;
use App\Models\LoanProductModel;
use App\Models\CustomerModel;
use CodeIgniter\API\ResponseTrait;

class LoanApplicationApi extends BaseController
{
    use ResponseTrait;

    protected LoanApplicationModel $applicationModel;
    protected LoanProductModel $productModel;
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->applicationModel = new LoanApplicationModel();
        $this->productModel     = new LoanProductModel();
        $this->customerModel    = new CustomerModel();
    }

    /**
     * List applications (can filter by ?status=Pending Review)
     */
    public function index()
    {
        $status = $this->request->getGet('status');
        $branchId = session()->get('role') === 'SUPER ADMIN' ? null : (int) session()->get('branch_id');
        $list   = $this->applicationModel->getApplicationsList($status, $branchId);

        return $this->respond([
            'status'  => 200,
            'message' => 'Loan applications retrieved',
            'data'    => $list,
        ]);
    }

    /**
     * Get details of a single application
     */
    public function show($id = null)
    {
        $app = $this->applicationModel->getDetailedApplication($id);
        if (! $app || ! user_can_access_branch((int) ($app['branch_id'] ?? 0))) {
            return $this->failNotFound('Loan application not found.');
        }

        return $this->respond([
            'status'  => 200,
            'message' => 'Loan application details',
            'data'    => $app,
        ]);
    }

    /**
     * Submit a new loan application (Status: Pending Review)
     */
    public function create()
    {
        $customerId     = (int)$this->request->getPost('customer_id');
        $productId      = (int)$this->request->getPost('loan_product_id');
        $amount         = (float)$this->request->getPost('amount_requested');
        $tenure         = (int)$this->request->getPost('tenure_months');

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            return $this->failValidationErrors(['customer_id' => 'Invalid customer selected.']);
        }

        $product = $this->productModel->find($productId);
        if (!$product || !$product['is_active']) {
            return $this->failValidationErrors(['loan_product_id' => 'Selected loan product is not available.']);
        }

        // Validate amount against product min/max
        if ($amount < (float)$product['min_amount'] || $amount > (float)$product['max_amount']) {
            return $this->failValidationErrors([
                'amount_requested' => "Loan amount must be between Rs. " . number_format($product['min_amount'], 2) . " and Rs. " . number_format($product['max_amount'], 2)
            ]);
        }

        // Validate tenure against product min/max
        if ($tenure < (int)$product['min_tenure_months'] || $tenure > (int)$product['max_tenure_months']) {
            return $this->failValidationErrors([
                'tenure_months' => "Tenure must be between {$product['min_tenure_months']} and {$product['max_tenure_months']} months."
            ]);
        }

        // Handle File Uploads (KYC & Income Proof)
        $kycDocPath    = null;
        $incomeDocPath = null;

        $uploadDir = WRITEPATH . 'uploads/loan_documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $kycFile = $this->request->getFile('kyc_doc');
        if ($kycFile && $kycFile->isValid() && !$kycFile->hasMoved()) {
            $newName = $kycFile->getRandomName();
            $kycFile->move($uploadDir, $newName);
            $kycDocPath = 'uploads/loan_documents/' . $newName;
        }

        $incomeFile = $this->request->getFile('income_doc');
        if ($incomeFile && $incomeFile->isValid() && !$incomeFile->hasMoved()) {
            $newName = $incomeFile->getRandomName();
            $incomeFile->move($uploadDir, $newName);
            $incomeDocPath = 'uploads/loan_documents/' . $newName;
        }

        $userId = session()->get('user_id');
        $branchId = (int) ($customer['branch_id'] ?? 0);
        if ($branchId <= 0 || ! user_can_access_branch($branchId)) {
            return $this->fail('The customer must belong to your branch.', 422);
        }

        $insertData = [
            'application_no'         => LoanApplicationModel::generateApplicationNumber(),
            'customer_id'            => $customerId,
            'loan_product_id'        => $productId,
            'branch_id'              => $branchId,
            'created_by_user_id'     => $userId,
            'amount_requested'       => $amount,
            'tenure_months'          => $tenure,
            'proposed_interest_rate' => (float)$product['interest_rate'],
            'purpose'                => trim((string)$this->request->getPost('purpose')),
            'monthly_income'         => (float)$this->request->getPost('monthly_income'),
            'collateral_type'        => $this->request->getPost('collateral_type'),
            'collateral_value'       => (float)$this->request->getPost('collateral_value'),
            'collateral_description' => trim((string)$this->request->getPost('collateral_description')),
            'guarantor_name'         => trim((string)$this->request->getPost('guarantor_name')),
            'guarantor_nic'          => trim((string)$this->request->getPost('guarantor_nic')),
            'guarantor_phone'        => trim((string)$this->request->getPost('guarantor_phone')),
            'guarantor_relationship' => trim((string)$this->request->getPost('guarantor_relationship')),
            'guarantor_address'      => trim((string)$this->request->getPost('guarantor_address')),
            'kyc_doc_path'           => $kycDocPath,
            'income_doc_path'        => $incomeDocPath,
            'status'                 => 'Pending Review',
            'remarks'                => trim((string)$this->request->getPost('remarks')),
        ];

        if (!$this->applicationModel->insert($insertData)) {
            return $this->failValidationErrors($this->applicationModel->errors());
        }

        $newId = $this->applicationModel->getInsertID();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Loan application submitted successfully and is now Pending Review.',
            'data'    => $this->applicationModel->getDetailedApplication($newId),
        ]);
    }
}
