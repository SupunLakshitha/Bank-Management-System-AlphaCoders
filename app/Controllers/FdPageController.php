<?php

namespace App\Controllers;

use App\Libraries\FdInterestCalculator;
use App\Models\FdProductModel;
use App\Services\FdCreationService;
use Config\Database;

class FdPageController extends BaseController
{
    private function base(string $title): array
    {
        return [
            'title'    => $title,
            'username' => session()->get('username'),
            'role'     => session()->get('role'),
        ];
    }

    // ---------- Story 7: FD Products ----------
    public function products()
    {
        $products = (new FdProductModel())->orderBy('id', 'DESC')->findAll();
        return view('fd/products', $this->base('FD Products') + ['products' => $products]);
    }

    public function productSave($id = null)
    {
        $model = new FdProductModel();
        $data  = [
            'name'                 => trim((string) $this->request->getPost('name')),
            'tenure_months'        => (int) $this->request->getPost('tenure_months'),
            'interest_rate'        => (float) $this->request->getPost('interest_rate'),
            'interest_type'        => $this->request->getPost('interest_type'),
            'compounding_per_year' => (int) ($this->request->getPost('compounding_per_year') ?: 4),
            'min_amount'           => (float) $this->request->getPost('min_amount'),
            'is_active'            => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            if (! $model->find($id)) {
                return redirect()->to('/fd/products')->with('error', 'FD product not found.');
            }
            $ok = $model->update($id, $data);
        } else {
            $ok = $model->insert($data);
        }

        if (! $ok) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/fd/products')->with('success', $id ? 'FD product updated.' : 'FD product created.');
    }

    public function productToggle($id = null)
    {
        $model   = new FdProductModel();
        $product = $model->find($id);
        if (! $product) {
            return redirect()->to('/fd/products')->with('error', 'FD product not found.');
        }

        $new = $product['is_active'] ? 0 : 1;
        $model->update($id, ['is_active' => $new]);

        return redirect()->to('/fd/products')->with('success', "'{$product['name']}' " . ($new ? 'activated.' : 'deactivated.'));
    }

    // ---------- Story 8: Open FD + Certificate ----------
    public function open()
    {
        $db            = Database::connect();
        $accountsReady = $db->tableExists('accounts');

        $data = $this->base('Open Fixed Deposit') + [
            'accountsReady' => $accountsReady,
            'customers'     => $db->table('customers')->get()->getResultArray(),
            'accounts'      => $accountsReady ? $db->table('accounts')->where('status', 'Active')->get()->getResultArray() : [],
            'products'      => (new FdProductModel())->where('is_active', 1)->findAll(),
        ];

        return view('fd/open', $data);
    }

    public function openSubmit()
    {
        $result = (new FdCreationService())->open(
            (int) $this->request->getPost('customer_id'),
            (int) $this->request->getPost('fd_product_id'),
            (int) $this->request->getPost('account_id'),
            (float) $this->request->getPost('amount')
        );

        if (! $result['ok']) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        $fd = Database::connect()->table('fixed_deposits')
            ->where('fd_number', $result['data']['fd_number'])->get()->getRowArray();

        return redirect()->to('/fd/certificate/' . $fd['id'])->with('success', 'Fixed deposit opened successfully.');
    }

    public function certificate($id = null)
    {
        $cert = (new FdCreationService())->certificate((int) $id);
        if (! $cert) {
            return redirect()->to('/fd/open')->with('error', 'FD not found.');
        }

        return view('fd/certificate', $this->base('FD Certificate') + ['cert' => $cert]);
    }

    // ---------- Story 19: Calculator ----------
    public function calculator()
    {
        $result = null;
        $error  = null;
        $input  = $this->request->getPost();

        if ($this->request->is('post')) {
            try {
                $result = (new FdInterestCalculator())->previewMaturity(
                    (float) ($input['principal'] ?? 0),
                    (float) ($input['rate'] ?? 0),
                    (int) ($input['tenure'] ?? 0),
                    ($input['start_date'] ?? '') ?: date('Y-m-d'),
                    $input['type'] ?? 'simple',
                    $input['convention'] ?? FdInterestCalculator::ACTUAL_365,
                    (int) ($input['times'] ?? 4)
                );
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('fd/calculator', $this->base('FD Interest Calculator') + [
            'result' => $result,
            'error'  => $error,
            'input'  => $input,
        ]);
    }

    // ---------- Story 20: Interest Logs ----------
    public function logs()
    {
        $db   = Database::connect();
        $logs = $db->table('interest_logs l')
            ->select('l.*, fd.fd_number, fd.principal')
            ->join('fixed_deposits fd', 'fd.id = l.fd_id')
            ->orderBy('l.accrual_date', 'DESC')
            ->orderBy('l.id', 'DESC')
            ->limit(200)
            ->get()->getResultArray();

        return view('fd/logs', $this->base('FD Interest Logs') + ['logs' => $logs]);
    }
}