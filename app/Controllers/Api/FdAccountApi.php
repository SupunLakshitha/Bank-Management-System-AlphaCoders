<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\FdCreationService;
use CodeIgniter\API\ResponseTrait;

class FdAccountApi extends BaseController
{
    use ResponseTrait;

    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        $rules = [
            'customer_id'    => 'required|is_natural_no_zero',
            'fd_product_id'  => 'required|is_natural_no_zero',
            'account_id'     => 'required|is_natural_no_zero',
            'amount'         => 'required|decimal|greater_than[0]',
        ];
        if (! $this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $result = (new FdCreationService())->open(
            (int) $data['customer_id'],
            (int) $data['fd_product_id'],
            (int) $data['account_id'],
            (float) $data['amount']
        );

        if (! $result['ok']) {
            return $this->fail($result['error'], $result['status']);
        }

        return $this->respondCreated($result['data']);
    }

    public function certificate($id = null)
    {
        $cert = (new FdCreationService())->certificate((int) $id);

        return $cert ? $this->respond($cert) : $this->failNotFound('FD not found.');
    }
}