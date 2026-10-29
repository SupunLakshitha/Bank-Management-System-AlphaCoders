<?php

namespace App\Controllers;

use App\Models\BranchModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Services\AuditService;

class EmployeeController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $employees = $db->table('users')
            ->select('users.id, users.username, users.role_id, users.branch_id, users.created_at, roles.name AS role_name, branches.name AS branch_name')
            ->join('roles', 'roles.id = users.role_id')
            ->join('branches', 'branches.id = users.branch_id', 'left')
            ->where('roles.name !=', 'Customer')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();

        return view('employees/index', [
            'username'  => session()->get('username'),
            'role'      => session()->get('role'),
            'myId'      => (int) session()->get('user_id'),
            'employees' => $employees,
            'roles'     => (new RoleModel())->where('name !=', 'Customer')->orderBy('id', 'ASC')->findAll(),
            'branches'  => (new BranchModel())->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        $rules = [
            'username'  => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'password'  => 'required|min_length[8]',
            'role_id'   => 'required|is_not_unique[roles.id]',
            'branch_id' => 'required|is_not_unique[branches.id]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = (new RoleModel())->find((int) $this->request->getPost('role_id'));
        if (! $role || $role['name'] === 'Customer') {
            return redirect()->back()->withInput()->with('error', 'Choose a staff role (Customer accounts are created from the public sign-up page).');
        }

        $users = new UserModel();
        $users->insert([
            'username'      => trim((string) $this->request->getPost('username')),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'       => $role['id'],
            'branch_id'     => (int) $this->request->getPost('branch_id'),
        ]);

        AuditService::log('Employees', 'CREATE', (string) $users->getInsertID(), 'Created employee with role ' . $role['name']);

        return redirect()->to('/employees')->with('success', 'Employee created with role ' . $role['name'] . '.');
    }

    public function update($id = null)
    {
        $id    = (int) $id;
        $users = new UserModel();
        $user  = $users->find($id);

        if (! $user) {
            return redirect()->to('/employees')->with('error', 'Employee not found.');
        }

        if ($id === (int) session()->get('user_id')) {
            return redirect()->to('/employees')->with('error', 'You cannot change your own role or branch.');
        }

        $role   = (new RoleModel())->find((int) $this->request->getPost('role_id'));
        $branch = (new BranchModel())->find((int) $this->request->getPost('branch_id'));

        if (! $role || $role['name'] === 'Customer' || ! $branch) {
            return redirect()->to('/employees')->with('error', 'Select a valid staff role and branch.');
        }

        $users->update($id, ['role_id' => $role['id'], 'branch_id' => $branch['id']]);

        AuditService::log('Employees', 'UPDATE', (string) $id, 'Role set to ' . $role['name'] . ', branch ' . $branch['name']);

        return redirect()->to('/employees')->with('success', $user['username'] . ' is now ' . $role['name'] . ' at ' . $branch['name'] . '. Takes effect on their next login.');
    }
}
