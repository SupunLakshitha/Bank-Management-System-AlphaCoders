<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LoanApplicationModel;
use App\Models\LoanProductModel;
use App\Models\LoanModel;
use App\Models\LoanRepaymentScheduleModel;
use App\Services\LedgerService;
use App\Services\EmiCalculatorService;

class LoanApprovalController extends BaseController
{
    protected LoanApplicationModel $applicationModel;
    protected LoanProductModel $productModel;
    protected LoanModel $loanModel;
    protected LoanRepaymentScheduleModel $scheduleModel;
    protected LedgerService $ledgerService;

    public function __construct()
    {
        $this->applicationModel = new LoanApplicationModel();
        $this->productModel     = new LoanProductModel();
        $this->loanModel        = new LoanModel();
        $this->scheduleModel    = new LoanRepaymentScheduleModel();
        $this->ledgerService    = new LedgerService();
    }

    /**
     * Approve and Disburse Loan Application (Branch Manager / Super Admin)
     */
    public function approve($applicationId = null)
    {
        $application = $this->applicationModel->find($applicationId);
        if (!$application) {
            return redirect()->to('/loans/applications')->with('error', 'Application not found.');
        }

        if (! user_can_access_branch((int) ($application['branch_id'] ?? 0))) {
            return redirect()->to('/loans/applications')->with('error', 'That application belongs to another branch.');
        }

        if ($application['status'] !== 'Pending Review') {
            return redirect()->back()->with('error', "Application is already in status '{$application['status']}'.");
        }

        $product = $this->productModel->find($application['loan_product_id']);
        $interestType = $product ? $product['interest_type'] : 'compound';

        // 1. Calculate EMI and Total Payable
        $financials = LoanModel::calculateLoanFinancials(
            (float)$application['amount_requested'],
            (float)$application['proposed_interest_rate'],
            (int)$application['tenure_months'],
            $interestType
        );

        $loanAccountNo = LoanModel::generateLoanAccountNumber();
        $approvedByUserId = session()->get('user_id') ?? 1;

        // 2. Create Active Loan record
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

        // 3. Generate & Save EMI Repayment Schedule into Database
        EmiCalculatorService::generateAndSaveSchedule($newLoanId);

        // 4. Update Application Status to Disbursed / Approved
        $this->applicationModel->update($applicationId, [
            'status'     => 'Disbursed',
            'remarks'    => ($application['remarks'] ? $application['remarks'] . "\n" : '') . "Approved & Disbursed by Manager on " . date('Y-m-d H:i'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // 5. Trigger Ledger Service (Dual Entry: Credit Savings & Debit Loan GL)
        $this->ledgerService->disburseLoanToSavings(
            (int)$application['customer_id'],
            (float)$application['amount_requested'],
            $loanAccountNo,
            $application['branch_id']
        );

        return redirect()->to('/loans/applications/view/' . $applicationId)
                         ->with('success', "Loan Application {$application['application_no']} approved successfully! Loan Account {$loanAccountNo} is now Active and amortization schedule has been generated.");
    }

    /**
     * Reject Loan Application (Branch Manager / Super Admin)
     */
    public function reject($applicationId = null)
    {
        $application = $this->applicationModel->find($applicationId);
        if (!$application) {
            return redirect()->to('/loans/applications')->with('error', 'Application not found.');
        }

        if (! user_can_access_branch((int) ($application['branch_id'] ?? 0))) {
            return redirect()->to('/loans/applications')->with('error', 'That application belongs to another branch.');
        }

        if ($application['status'] !== 'Pending Review') {
            return redirect()->back()->with('error', "Application is already in status '{$application['status']}'.");
        }

        $reason = trim((string)$this->request->getPost('rejection_reason'));
        if (empty($reason)) {
            $reason = 'Does not meet bank underwriting / credit eligibility criteria.';
        }

        $this->applicationModel->update($applicationId, [
            'status'     => 'Rejected',
            'remarks'    => ($application['remarks'] ? $application['remarks'] . "\n" : '') . "REJECTION REASON: " . $reason,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/loans/applications/view/' . $applicationId)
                         ->with('success', "Loan Application {$application['application_no']} was rejected.");
    }

    /**
     * View all Active Disbursed Loans
     */
    public function activeLoans()
    {
        $data = [
            'username'    => session()->get('username'),
            'role'        => session()->get('role'),
            'activeLoans' => $this->loanModel->getActiveLoans(session()->get('role') === 'SUPER ADMIN' ? null : (int) session()->get('branch_id')),
        ];

        return view('loans/index', $data);
    }

    /**
     * View Amortization Repayment Schedule for a Disbursed Loan
     */
    public function schedule($loanId = null)
    {
        $loan = $this->loanModel->getDetailedLoan((int)$loanId);
        if (!$loan || ! user_can_access_branch((int) ($loan['branch_id'] ?? 0))) {
            return redirect()->to('/loans/active')->with('error', 'Loan account not found.');
        }

        $schedules = $this->scheduleModel->getScheduleForLoan((int)$loanId);
        if (empty($schedules)) {
            // Auto generate if missing
            EmiCalculatorService::generateAndSaveSchedule((int)$loanId);
            $schedules = $this->scheduleModel->getScheduleForLoan((int)$loanId);
        }

        $data = [
            'username'  => session()->get('username'),
            'role'      => session()->get('role'),
            'loan'      => $loan,
            'schedules' => $schedules,
        ];

        return view('loans/schedule', $data);
    }
}
