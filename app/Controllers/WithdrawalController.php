<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\WithdrawalModel;
use App\Services\DepositWithdrawalService;

class WithdrawalController extends BaseController
{
    protected $withdrawalModel;
    protected $accountModel;
    protected $operationService;

    public function __construct()
    {
        $this->withdrawalModel  = new WithdrawalModel();
        $this->accountModel     = new AccountModel();
        $this->operationService = new DepositWithdrawalService();
    }

    public function index()
    {
        $query    = $this->request->getGet('q');
        $method   = $this->request->getGet('method');
        $fromDate = $this->request->getGet('from');
        $toDate   = $this->request->getGet('to');

        $withdrawals = $this->withdrawalModel->searchWithdrawals($query, $method, $fromDate, $toDate);

        $totalWithdrawn = 0.0;
        foreach ($withdrawals as $w) {
            $totalWithdrawn += (float)$w['amount'];
        }

        return view('withdrawals/index', [
            'title'          => 'Withdrawals History & Teller Journal',
            'withdrawals'    => $withdrawals,
            'totalWithdrawn' => $totalWithdrawn,
            'filters'        => [
                'q'      => $query,
                'method' => $method,
                'from'   => $fromDate,
                'to'     => $toDate,
            ],
        ]);
    }

    public function create()
    {
        $accountNo = $this->request->getGet('account_no');
        $selectedAccount = null;
        if ($accountNo) {
            $selectedAccount = $this->accountModel->getAccountWithDetails($accountNo);
        }

        return view('withdrawals/create', [
            'title'           => 'Teller Withdrawal Terminal',
            'selectedAccount' => $selectedAccount,
        ]);
    }

    public function createSubmit()
    {
        $accountNo   = trim($this->request->getPost('account_no'));
        $amount      = (float)$this->request->getPost('amount');
        $method      = $this->request->getPost('withdrawal_method') ?? 'Cash';
        $referenceNo = $this->request->getPost('reference_number');
        $remarks     = $this->request->getPost('remarks');
        $tellerId    = session()->get('user_id');

        $res = $this->operationService->processWithdrawal(
            $accountNo,
            $amount,
            $method,
            $tellerId,
            $referenceNo,
            $remarks
        );

        if (!$res['ok']) {
            return redirect()->back()->withInput()->with('error', $res['error']);
        }

        return redirect()->to(base_url('withdrawals/receipt/' . $res['id']))
            ->with('success', "Withdrawal of Rs. " . number_format($amount, 2) . " processed successfully!");
    }

    public function receipt($id)
    {
        $withdrawal = $this->withdrawalModel->getWithdrawalWithDetails((int)$id);
        if (!$withdrawal) {
            return redirect()->to(base_url('withdrawals'))->with('error', 'Withdrawal record not found.');
        }

        return view('withdrawals/receipt', [
            'title'      => 'Withdrawal Receipt - ' . $withdrawal['withdrawal_id'],
            'withdrawal' => $withdrawal,
        ]);
    }
}
