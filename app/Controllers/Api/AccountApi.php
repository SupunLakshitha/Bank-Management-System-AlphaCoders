<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Services\AccountService;
use CodeIgniter\HTTP\ResponseInterface;

class AccountApi extends BaseController
{
    protected $accountModel;
    protected $accountService;

    public function __construct()
    {
        $this->accountModel   = new AccountModel();
        $this->accountService = new AccountService();
    }

    public function lookup($accountNo)
    {
        $account = $this->accountModel->getAccountWithDetails($accountNo);
        if (!$account) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status'  => 'error',
                'message' => 'Account not found',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'id'            => (int)$account['id'],
                'account_no'    => $account['account_no'],
                'customer_id'   => (int)$account['customer_id'],
                'customer_name' => $account['customer_name'],
                'customer_code' => $account['cust_code'],
                'customer_nic'  => $account['customer_nic'],
                'account_type'  => $account['account_type_name'],
                'balance'       => (float)$account['balance'],
                'min_balance'   => (float)$account['min_balance'],
                'status'        => $account['status'],
                'branch_name'   => $account['branch_name'] ?? 'Main',
            ],
        ]);
    }

    public function byCustomer($customerId)
    {
        $accounts = $this->accountModel->getAccountsByCustomer((int)$customerId);

        return $this->response->setJSON([
            'status' => 'success',
            'count'  => count($accounts),
            'data'   => $accounts,
        ]);
    }

    public function open()
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $tellerId = session()->get('user_id');

        $res = $this->accountService->openAccount($input, $tellerId);

        if (!$res['ok']) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => isset($res['errors']) ? implode(', ', $res['errors']) : ($res['error'] ?? 'Failed to open account'),
            ]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)->setJSON([
            'status'  => 'success',
            'message' => 'Account opened successfully',
            'data'    => $res,
        ]);
    }
}
