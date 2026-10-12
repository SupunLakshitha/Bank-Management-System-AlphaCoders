<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\DepositModel;
use App\Services\DepositWithdrawalService;

class DepositController extends BaseController
{
    protected $depositModel;
    protected $accountModel;
    protected $operationService;

    public function __construct()
    {
        $this->depositModel     = new DepositModel();
        $this->accountModel     = new AccountModel();
        $this->operationService = new DepositWithdrawalService();
    }

    public function index()
    {
        $query    = $this->request->getGet('q');
        $method   = $this->request->getGet('method');
        $fromDate = $this->request->getGet('from');
        $toDate   = $this->request->getGet('to');

        $deposits = $this->depositModel->searchDeposits($query, $method, $fromDate, $toDate);

        $totalDeposited = 0.0;
        foreach ($deposits as $d) {
            $totalDeposited += (float)$d['amount'];
        }

        return view('deposits/index', [
            'title'          => 'Deposits History & Teller Journal',
            'deposits'       => $deposits,
            'totalDeposited' => $totalDeposited,
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

        return view('deposits/create', [
            'title'           => 'Teller Deposit Terminal',
            'selectedAccount' => $selectedAccount,
        ]);
    }

    public function createSubmit()
    {
        $accountNo   = trim($this->request->getPost('account_no'));
        $amount      = (float)$this->request->getPost('amount');
        $method      = $this->request->getPost('deposit_method') ?? 'Cash';
        $referenceNo = $this->request->getPost('reference_number');
        $remarks     = $this->request->getPost('remarks');
        $tellerId    = session()->get('user_id');

        $res = $this->operationService->processDeposit(
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

        return redirect()->to(base_url('deposits/receipt/' . $res['id']))
            ->with('success', "Deposit of Rs. " . number_format($amount, 2) . " processed successfully!");
    }

    public function receipt($id)
    {
        $deposit = $this->depositModel->getDepositWithDetails((int)$id);
        if (!$deposit) {
            return redirect()->to(base_url('deposits'))->with('error', 'Deposit record not found.');
        }

        return view('deposits/receipt', [
            'title'   => 'Deposit Receipt - ' . $deposit['deposit_id'],
            'deposit' => $deposit,
        ]);
    }
}
