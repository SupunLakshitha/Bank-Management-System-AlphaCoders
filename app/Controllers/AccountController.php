<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\AccountTypeModel;
use App\Models\BranchModel;
use App\Models\CustomerModel;
use App\Services\AccountService;

class AccountController extends BaseController
{
    protected $accountModel;
    protected $accountTypeModel;
    protected $customerModel;
    protected $branchModel;
    protected $accountService;

    public function __construct()
    {
        $this->accountModel     = new AccountModel();
        $this->accountTypeModel = new AccountTypeModel();
        $this->customerModel    = new CustomerModel();
        $this->branchModel      = new BranchModel();
        $this->accountService   = new AccountService();
    }

    public function index()
    {
        $query    = $this->request->getGet('q');
        $status   = $this->request->getGet('status');
        $typeId   = $this->request->getGet('type_id');
        $branchId = $this->request->getGet('branch_id');

        $accounts = $this->accountModel->searchAccounts(
            $query,
            $status,
            $typeId ? (int)$typeId : null,
            $branchId ? (int)$branchId : null
        );

        $types    = $this->accountTypeModel->findAll();
        $branches = $this->branchModel->findAll();

        $totalDepositsSum = 0.0;
        foreach ($accounts as $acc) {
            $totalDepositsSum += (float)$acc['balance'];
        }

        return view('accounts/index', [
            'title'            => 'Bank Accounts Management',
            'accounts'         => $accounts,
            'types'            => $types,
            'branches'         => $branches,
            'totalDepositsSum' => $totalDepositsSum,
            'filters'          => [
                'q'         => $query,
                'status'    => $status,
                'type_id'   => $typeId,
                'branch_id' => $branchId,
            ],
        ]);
    }

    public function open()
    {
        $customerId = $this->request->getGet('customer_id');
        $selectedCustomer = null;
        if ($customerId) {
            $selectedCustomer = $this->customerModel->find((int)$customerId);
        }

        $customers = $this->customerModel->orderBy('full_name', 'ASC')->findAll();
        $types     = $this->accountTypeModel->getActiveTypes();
        $branches  = $this->branchModel->findAll();

        return view('accounts/open', [
            'title'            => 'Open Bank Account',
            'customers'        => $customers,
            'types'            => $types,
            'branches'         => $branches,
            'selectedCustomer' => $selectedCustomer,
        ]);
    }

    public function openSubmit()
    {
        $tellerId = session()->get('user_id');

        $data = [
            'customer_id'     => $this->request->getPost('customer_id'),
            'account_type_id' => $this->request->getPost('account_type_id'),
            'branch_id'       => $this->request->getPost('branch_id'),
            'initial_deposit' => $this->request->getPost('initial_deposit') ?? 0,
            'deposit_method'  => $this->request->getPost('deposit_method') ?? 'Cash',
            'interest_rate'   => $this->request->getPost('interest_rate'),
        ];

        $res = $this->accountService->openAccount($data, $tellerId);

        if (!$res['ok']) {
            $errorMsg = isset($res['errors']) ? implode(', ', $res['errors']) : ($res['error'] ?? 'Failed to open account.');
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }

        return redirect()->to(base_url('accounts/view/' . $res['account_id']))
            ->with('success', "Account {$res['account_no']} successfully opened!");
    }

    public function view($id)
    {
        $statement = $this->accountService->getAccountStatement((int)$id);
        if (!$statement) {
            return redirect()->to(base_url('accounts'))->with('error', 'Account not found.');
        }

        return view('accounts/view', [
            'title'   => 'Account Details - ' . $statement['account']['account_no'],
            'account' => $statement['account'],
            'history' => $statement['history'],
        ]);
    }

    public function toggleStatus($id)
    {
        $status = $this->request->getPost('status');
        $res = $this->accountService->updateStatus((int)$id, $status);

        if (!$res['ok']) {
            return redirect()->back()->with('error', $res['error']);
        }

        return redirect()->back()->with('success', $res['message']);
    }
}
