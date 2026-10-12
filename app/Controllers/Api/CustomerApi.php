<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Services\CustomerService;
use CodeIgniter\HTTP\ResponseInterface;

class CustomerApi extends BaseController
{
    protected $customerModel;
    protected $customerService;

    public function __construct()
    {
        $this->customerModel   = new CustomerModel();
        $this->customerService = new CustomerService();
    }

    public function search()
    {
        $query = $this->request->getGet('q');
        $customers = $this->customerModel->searchCustomers($query);

        return $this->response->setJSON([
            'status' => 'success',
            'count'  => count($customers),
            'data'   => $customers,
        ]);
    }

    public function get($id)
    {
        $profile = $this->customerService->getCustomerProfile((int)$id);
        if (!$profile) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON([
                'status' => 'error',
                'message'=> 'Customer not found',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $profile,
        ]);
    }

    public function updateKyc($id)
    {
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        $status = $input['kyc_status'] ?? '';

        $res = $this->customerService->updateKycStatus((int)$id, $status);
        if (!$res['ok']) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)->setJSON([
                'status'  => 'error',
                'message' => $res['error'],
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $res['message'],
        ]);
    }
}
