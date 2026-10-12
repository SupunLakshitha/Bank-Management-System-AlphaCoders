<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BranchModel;
use App\Models\UserModel;

class BranchController extends BaseController
{
    protected BranchModel $branchModel;

    public function __construct()
    {
        $this->branchModel = new BranchModel();
    }

    public function index()
    {
        if ($denied = $this->denyCustomer()) {
            return $denied;
        }

        $onlyBranchId = session()->get('role') === 'SUPER ADMIN'
            ? null
            : session_branch_id();

        return view('branches/index', [
            'username' => session()->get('username'),
            'role'     => session()->get('role'),
            'branches' => $this->branchModel->withCounts($onlyBranchId),
        ]);
    }

    public function show($id = null)
    {
        if ($denied = $this->denyCustomer()) {
            return $denied;
        }

        $branch = $this->branchModel->find((int) $id);
        if (! $branch) {
            return redirect()->to('/branches')->with('error', 'Branch not found.');
        }
        if (! user_can_access_branch((int) $branch['id'])) {
            $ownBranch = session()->get('branch_name') ?: 'your own branch';

            return redirect()->to('/branches')->with('error', $branch['name'] . ' is outside your access. You can open ' . $ownBranch . '.');
        }

        $db = db_connect();

        $employees = $db->table('users')
            ->select('users.id, users.username, roles.name AS role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.branch_id', $branch['id'])
            ->where('roles.name !=', 'Customer')
            ->orderBy('roles.name', 'ASC')
            ->get()
            ->getResultArray();

        $customers = $db->table('customers')
            ->select('customers.customer_id, customers.full_name, customers.phone, customers.kyc_status, users.username')
            ->join('users', 'users.id = customers.user_id', 'left')
            ->where('customers.branch_id', $branch['id'])
            ->orderBy('customers.full_name', 'ASC')
            ->get()
            ->getResultArray();

        $accounts = $db->table('accounts')
            ->select('accounts.account_no, accounts.account_type, accounts.balance, accounts.status, customers.full_name, customers.customer_id')
            ->join('customers', 'customers.id = accounts.customer_id')
            ->where('accounts.branch_id', $branch['id'])
            ->orderBy('accounts.account_no', 'ASC')
            ->get()
            ->getResultArray();

        $transactions = $db->table('transactions')
            ->select('transactions.tx_id, transactions.type, transactions.amount, transactions.description, transactions.created_at, accounts.account_no, customers.full_name')
            ->join('accounts', 'accounts.id = transactions.account_id')
            ->join('customers', 'customers.id = accounts.customer_id')
            ->where('transactions.branch_id', $branch['id'])
            ->orderBy('transactions.id', 'DESC')
            ->limit(50)
            ->get()
            ->getResultArray();

        $loans = $db->table('loans')
            ->select('loans.loan_account_no, loans.principal_amount, loans.status, customers.full_name, customers.customer_id')
            ->join('customers', 'customers.id = loans.customer_id')
            ->where('loans.branch_id', $branch['id'])
            ->orderBy('loans.id', 'DESC')
            ->get()
            ->getResultArray();

        $unassignedEmployees = [];
        if (session()->get('role') === 'SUPER ADMIN') {
            $unassignedEmployees = $db->table('users')
                ->select('users.id, users.username, roles.name AS role_name, branches.name AS branch_name')
                ->join('roles', 'roles.id = users.role_id')
                ->join('branches', 'branches.id = users.branch_id', 'left')
                ->where('roles.name !=', 'Customer')
                ->where('users.branch_id !=', $branch['id'])
                ->orderBy('users.username', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('branches/show', [
            'username'             => session()->get('username'),
            'role'                 => session()->get('role'),
            'branch'               => $branch,
            'employees'            => $employees,
            'customers'            => $customers,
            'accounts'             => $accounts,
            'transactions'         => $transactions,
            'loans'                => $loans,
            'unassignedEmployees'  => $unassignedEmployees,
        ]);
    }

    public function create()
    {
        if (session()->get('role') !== 'SUPER ADMIN') {
            return redirect()->to('/branches')->with('error', 'Only a super admin can add a branch.');
        }

        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $this->request->setGlobal('post', array_merge($this->request->getPost(), ['code' => $code]));

        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'code'     => 'required|alpha_numeric|min_length[2]|max_length[10]|is_unique[branches.code]',
            'location' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->branchModel->insert([
            'name'     => trim((string) $this->request->getPost('name')),
            'code'     => $code,
            'location' => trim((string) $this->request->getPost('location')),
        ]);

        return redirect()->to('/branches')->with('success', 'Branch added. New customers, accounts, employees, transactions, and loans can now be assigned to it.');
    }

    public function assignEmployee($id = null)
    {
        if (session()->get('role') !== 'SUPER ADMIN') {
            return redirect()->to('/branches')->with('error', 'Only a super admin can move employees between branches.');
        }

        $branch = $this->branchModel->find((int) $id);
        if (! $branch) {
            return redirect()->to('/branches')->with('error', 'Branch not found.');
        }

        $userId = (int) $this->request->getPost('user_id');
        $user   = db_connect()->table('users')
            ->select('users.id, users.username, roles.name AS role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->get()
            ->getRowArray();

        if (! $user || $user['role_name'] === 'Customer') {
            return redirect()->back()->with('error', 'Select an employee to assign to this branch.');
        }

        (new UserModel())->update($userId, ['branch_id' => $branch['id']]);

        return redirect()->to('/branches/' . $branch['id'])
            ->with('success', $user['username'] . ' is now assigned to ' . $branch['name'] . '. They will see this branch after their next login.');
    }

    private function denyCustomer()
    {
        if (session()->get('role') === 'Customer') {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to access that page.');
        }

        return null;
    }
}
