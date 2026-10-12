<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\CustomerModel;
use App\Services\CustomerService;

class CustomerController extends BaseController
{
    protected $customerModel;
    protected $branchModel;
    protected $customerService;

    public function __construct()
    {
        $this->customerModel   = new CustomerModel();
        $this->branchModel     = new BranchModel();
        $this->customerService = new CustomerService();
    }

    public function index()
    {
        $query     = $this->request->getGet('q');
        $kycStatus = $this->request->getGet('kyc_status');
        $branchId  = $this->request->getGet('branch_id');

        $customers = $this->customerModel->searchCustomers($query, $kycStatus, $branchId ? (int)$branchId : null);
        $branches  = $this->branchModel->findAll();

        $stats = [
            'total'    => $this->customerModel->countAllResults(false),
            'verified' => $this->customerModel->where('kyc_status', 'Verified')->countAllResults(false),
            'pending'  => $this->customerModel->where('kyc_status', 'Pending')->countAllResults(false),
            'rejected' => $this->customerModel->where('kyc_status', 'Rejected')->countAllResults(false),
        ];

        return view('customers/index', [
            'title'     => 'Customer Profiles & KYC',
            'customers' => $customers,
            'branches'  => $branches,
            'stats'     => $stats,
            'filters'   => [
                'q'          => $query,
                'kyc_status' => $kycStatus,
                'branch_id'  => $branchId,
            ],
        ]);
    }

    public function create()
    {
        $branches = $this->branchModel->findAll();
        $generatedId = $this->customerModel->generateCustomerId();

        return view('customers/create', [
            'title'       => 'Onboard New Customer',
            'branches'    => $branches,
            'generatedId' => $generatedId,
        ]);
    }

    public function createSubmit()
    {
        $data = [
            'customer_id' => $this->request->getPost('customer_id'),
            'full_name'   => $this->request->getPost('full_name'),
            'nic'         => $this->request->getPost('nic'),
            'dob'         => $this->request->getPost('dob'),
            'phone'       => $this->request->getPost('phone'),
            'email'       => $this->request->getPost('email'),
            'address'     => $this->request->getPost('address'),
            'occupation'  => $this->request->getPost('occupation'),
            'kyc_status'  => $this->request->getPost('kyc_status') ?? 'Pending',
            'branch_id'   => $this->request->getPost('branch_id'),
        ];

        $createLogin = (bool)$this->request->getPost('create_login');
        $username    = $this->request->getPost('username');
        $password    = $this->request->getPost('password');

        if ($createLogin) {
            $data['username'] = $username;
        }

        $res = $this->customerService->createCustomer($data, $createLogin, $password);

        if (!$res['ok']) {
            $errorMessage = isset($res['errors']) ? implode(', ', $res['errors']) : ($res['error'] ?? 'Failed to create customer');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }

        return redirect()->to(base_url('customers/view/' . $res['id']))->with('success', "Customer {$res['customer_id']} registered successfully.");
    }

    public function view($id)
    {
        $profile = $this->customerService->getCustomerProfile((int)$id);
        if (!$profile) {
            return redirect()->to(base_url('customers'))->with('error', 'Customer not found.');
        }

        return view('customers/view', [
            'title'    => 'Customer Profile - ' . $profile['customer']['customer_id'],
            'customer' => $profile['customer'],
            'accounts' => $profile['accounts'],
            'summary'  => [
                'total_balance' => $profile['total_balance'],
                'account_count' => $profile['account_count'],
            ],
        ]);
    }

    public function edit($id)
    {
        $customer = $this->customerModel->find((int)$id);
        if (!$customer) {
            return redirect()->to(base_url('customers'))->with('error', 'Customer not found.');
        }

        $branches = $this->branchModel->findAll();

        return view('customers/edit', [
            'title'    => 'Edit Customer - ' . $customer['customer_id'],
            'customer' => $customer,
            'branches' => $branches,
        ]);
    }

    public function editSubmit($id)
    {
        $data = [
            'full_name'   => $this->request->getPost('full_name'),
            'nic'         => $this->request->getPost('nic'),
            'dob'         => $this->request->getPost('dob'),
            'phone'       => $this->request->getPost('phone'),
            'email'       => $this->request->getPost('email'),
            'address'     => $this->request->getPost('address'),
            'occupation'  => $this->request->getPost('occupation'),
            'kyc_status'  => $this->request->getPost('kyc_status'),
            'branch_id'   => $this->request->getPost('branch_id'),
        ];

        $res = $this->customerService->updateCustomer((int)$id, $data);

        if (!$res['ok']) {
            $errorMessage = isset($res['errors']) ? implode(', ', $res['errors']) : ($res['error'] ?? 'Failed to update customer');
            return redirect()->back()->withInput()->with('error', $errorMessage);
        }

        return redirect()->to(base_url('customers/view/' . $id))->with('success', 'Customer profile updated successfully.');
    }

    public function kycSubmit($id)
    {
        $status = $this->request->getPost('kyc_status');
        $res = $this->customerService->updateKycStatus((int)$id, $status);

        if (!$res['ok']) {
            return redirect()->back()->with('error', $res['error']);
        }

        return redirect()->back()->with('success', $res['message']);
    }
}
