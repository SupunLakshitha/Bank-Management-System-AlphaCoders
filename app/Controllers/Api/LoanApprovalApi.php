<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LoanApplicationModel;
use App\Models\LoanProductModel;
use App\Models\LoanModel;
use App\Services\LedgerService;
use App\Services\EmiCalculatorService;
use CodeIgniter\API\ResponseTrait;

class LoanApprovalApi extends BaseController
{
    use ResponseTrait;

    protected LoanApplicationModel $applicationModel;
    protected LoanProductModel $productModel;
    protected LoanModel $loanModel;
    protected LedgerService $ledgerService;

    public function __construct()
    {
        $this->applicationModel = new LoanApplicationModel();
        $this->productModel     = new LoanProductModel();
        $this->loanModel        = new LoanModel();
        $this->ledgerService    = new LedgerService();
    }

    /**
     * Approve and Disburse Loan via API
     */
    public function approve($id = null)
    {
        $application = $this->applicationModel->find($id);
        if (!$application) {
            return $this->failNotFound('Loan application not found.');
        }

        if (! user_can_access_branch((int) ($application['branch_id'] ?? 0))) {
            return $this->failForbidden('That application belongs to another branch.');
        }

        if ($application['status'] !== 'Pending Review') {
            return $this->fail([
                'status'  => 400,
                'message' => "Cannot approve application in status '{$application['status']}'."
            ]);
        }

        $product = $this->productModel->find($application['loan_product_id']);
        $interestType = $product ? $product['interest_type'] : 'compound';

        $financials = LoanModel::calculateLoanFinancials(
            (float)$application['amount_requested'],
            (float)$application['proposed_interest_rate'],
            (int)$application['tenure_months'],
            $interestType
        );

        $loanAccountNo = LoanModel::generateLoanAccountNumber();
        $approvedByUserId = session()->get('user_id') ?? 1;

        $loanData = [
            'loan_account_no'     => $loanAccountNo,
            'application_id'      => $application['id'],
            'customer_id'         => $application['customer_id'],
            'loan_product_id'     => $application['loan_product_id'],
            'branch_id'           => $application['branch_id'],
            'approved_by_user_id' => $approvedByUserId,
            'principal_amount'    => (float)$application['amount_requested'],
            'interest_rate'       => (float)$application['proposed_interest_rate'],
            'tenure_months'       => (int)$application['tenure_months'],
            'emi_amount'          => $financials['emi'],
            'total_payable'       => $financials['total_payable'],
            'total_paid'          => 0.00,
            'outstanding_balance' => (float)$application['amount_requested'],
            'disbursed_at'        => date('Y-m-d H:i:s'),
            'status'              => 'Active',
        ];

        $this->loanModel->insert($loanData);
        $newLoanId = $this->loanModel->getInsertID();

        // Generate and save repayment schedule
        $scheduleResult = EmiCalculatorService::generateAndSaveSchedule($newLoanId);

        // Update application
        $this->applicationModel->update($id, [
            'status'     => 'Disbursed',
            'remarks'    => ($application['remarks'] ? $application['remarks'] . "\n" : '') . "Approved & Disbursed on " . date('Y-m-d H:i'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Trigger Ledger Service
        $ledgerReceipt = $this->ledgerService->disburseLoanToSavings(
            (int)$application['customer_id'],
            (float)$application['amount_requested'],
            $loanAccountNo,
            $application['branch_id']
        );

        return $this->respond([
            'status'         => 200,
            'message'        => 'Loan application approved, schedule created, and funds disbursed successfully.',
            'loan'           => $this->loanModel->getDetailedLoan($newLoanId),
            'schedule_count' => count($scheduleResult['schedule'] ?? []),
            'ledger_receipt' => $ledgerReceipt,
        ]);
    }

    /**
     * Reject Loan via API
     */
    public function reject($id = null)
    {
        $application = $this->applicationModel->find($id);
        if (!$application) {
            return $this->failNotFound('Loan application not found.');
        }

        if (! user_can_access_branch((int) ($application['branch_id'] ?? 0))) {
            return $this->failForbidden('That application belongs to another branch.');
        }

        if ($application['status'] !== 'Pending Review') {
            return $this->fail([
                'status'  => 400,
                'message' => "Cannot reject application in status '{$application['status']}'."
            ]);
        }

        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $reason = $input['rejection_reason'] ?? 'Does not meet bank credit policy.';

        $this->applicationModel->update($id, [
            'status'     => 'Rejected',
            'remarks'    => ($application['remarks'] ? $application['remarks'] . "\n" : '') . "REJECTION REASON: " . $reason,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->respond([
            'status'  => 200,
            'message' => 'Loan application rejected.',
            'data'    => $this->applicationModel->find($id),
        ]);
    }

    /**
     * Fetch all active running loans
     */
    public function activeLoans()
    {
        return $this->respond([
            'status'  => 200,
            'message' => 'Active loans retrieved',
            'data'    => $this->loanModel->getActiveLoans(session()->get('role') === 'SUPER ADMIN' ? null : (int) session()->get('branch_id')),
        ]);
    }
}
