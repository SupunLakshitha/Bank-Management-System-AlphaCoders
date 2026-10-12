<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\DepositWithdrawalService;
use CodeIgniter\HTTP\ResponseInterface;

class TransactionOperationApi extends BaseController
{
    protected $operationService;

    public function __construct()
    {
        $this->operationService = new DepositWithdrawalService();
    }

    public function deposit()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $accountNo   = trim($input['account_no'] ?? '');
        $amount      = (float)($input['amount'] ?? 0);
        $method      = $input['deposit_method'] ?? 'Cash';
        $referenceNo = $input['reference_number'] ?? null;
        $remarks     = $input['remarks'] ?? null;
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
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => $res['error'],
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Deposit successful',
            'data'    => $res,
        ]);
    }

    public function withdraw()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();

        $accountNo   = trim($input['account_no'] ?? '');
        $amount      = (float)($input['amount'] ?? 0);
        $method      = $input['withdrawal_method'] ?? 'Cash';
        $referenceNo = $input['reference_number'] ?? null;
        $remarks     = $input['remarks'] ?? null;
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
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => $res['error'],
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Withdrawal successful',
            'data'    => $res,
        ]);
    }
}
