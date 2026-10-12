<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LoanApplicationModel;
use App\Models\LoanProductModel;
use App\Models\CustomerModel;

class LoanApplicationController extends BaseController
{
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
     * Applications Queue / List
     */
    public function index()
    {
        $statusFilter = $this->request->getGet('status');

        $data = [
            'username'     => session()->get('username'),
            'role'         => session()->get('role'),
            'applications' => $this->applicationModel->getApplicationsList($statusFilter, $this->branchScope()),
            'statusFilter' => $statusFilter,
        ];

        return view('loan_applications/index', $data);
    }

    /**
     * Multi-step Loan Application Form
     */
    public function apply()
    {
        $customers = $this->customerModel
            ->select('customers.*, branches.name as branch_name')
            ->join('branches', 'branches.id = customers.branch_id')
            ->orderBy('customers.full_name', 'ASC');
        if ($this->branchScope() !== null) {
            $customers->where('customers.branch_id', $this->branchScope());
        }

        $data = [
            'username'  => session()->get('username'),
            'role'      => session()->get('role'),
            'customers' => $customers->findAll(),
            'products'  => $this->productModel->getActiveProducts(),
        ];

        return view('loan_applications/apply', $data);
    }

    /**
     * Process Form Submission (Story 6)
     */
    public function submit()
    {
        $customerId = (int)$this->request->getPost('customer_id');
        $productId  = (int)$this->request->getPost('loan_product_id');
        $amount     = (float)$this->request->getPost('amount_requested');
        $tenure     = (int)$this->request->getPost('tenure_months');

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            return redirect()->back()->withInput()->with('errors', ['customer_id' => 'Please select a valid customer.']);
        }

        $product = $this->productModel->find($productId);
        if (!$product || !$product['is_active']) {
            return redirect()->back()->withInput()->with('errors', ['loan_product_id' => 'The selected loan product is not active.']);
        }

        // Validate Amount
        if ($amount < (float)$product['min_amount'] || $amount > (float)$product['max_amount']) {
            return redirect()->back()->withInput()->with('errors', [
                'amount_requested' => 'Loan amount must be between Rs. ' . number_format($product['min_amount'], 2) . ' and Rs. ' . number_format($product['max_amount'], 2)
            ]);
        }

        // Validate Tenure
        if ($tenure < (int)$product['min_tenure_months'] || $tenure > (int)$product['max_tenure_months']) {
            return redirect()->back()->withInput()->with('errors', [
                'tenure_months' => "Tenure must be between {$product['min_tenure_months']} and {$product['max_tenure_months']} months."
            ]);
        }

        // Handle File Uploads (KYC Document & Income Proof)
        $uploadDir = WRITEPATH . 'uploads/loan_documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $kycDocPath    = null;
        $incomeDocPath = null;

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

        $userId   = session()->get('user_id');
        $branchId = (int) ($customer['branch_id'] ?? 0);
        if ($branchId <= 0) {
            return redirect()->back()->withInput()->with('errors', ['customer_id' => 'This customer is not assigned to a branch.']);
        }
        if (! user_can_access_branch($branchId)) {
            return redirect()->back()->withInput()->with('errors', ['customer_id' => 'You can only submit applications for customers of your branch.']);
        }

        $data = [
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

        if (!$this->applicationModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->applicationModel->errors());
        }

        $appNo = $data['application_no'];
        return redirect()->to('/loans/applications')->with('success', "Loan Application {$appNo} submitted successfully and marked as 'Pending Review'!");
    }

    /**
     * View Single Application Details
     */
    public function view($id = null)
    {
        $application = $this->applicationModel->getDetailedApplication($id);

        if (!$application || ! user_can_access_branch((int) ($application['branch_id'] ?? 0))) {
            return redirect()->to('/loans/applications')->with('error', 'Loan application not found.');
        }

        $data = [
            'username'    => session()->get('username'),
            'role'        => session()->get('role'),
            'application' => $application,
        ];

        return view('loan_applications/view', $data);
    }

    private function branchScope(): ?int
    {
        if (session()->get('role') === 'SUPER ADMIN') {
            return null;
        }

        return (int) session()->get('branch_id');
    }
}
