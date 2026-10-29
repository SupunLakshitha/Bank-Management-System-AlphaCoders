<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\BranchModel;
use App\Models\CustomerModel;
use App\Models\AccountModel;
use App\Services\AuditService;
use App\Services\LoginThrottle;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function loginSubmit()
    {
        $userModel = new UserModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $ip       = $this->request->getIPAddress();

        $wait = LoginThrottle::secondsLeft((string) $username, $ip);
        if ($wait > 0) {
            return redirect()->back()->with('error', 'Too many failed login attempts. Please try again in ' . (int) ceil($wait / 60) . ' minute(s).');
        }

        $user = $userModel->where('username', $username)->first();

        if ($user && password_verify($password, $user['password_hash'])) {
            $roleModel = new RoleModel();
            $role = $roleModel->find($user['role_id']);
            $branch = ! empty($user['branch_id']) ? (new BranchModel())->find($user['branch_id']) : null;
            
            LoginThrottle::clear((string) $username, $ip);

            session()->regenerate();
            session()->set([
                'user_id'     => $user['id'],
                'username'    => $user['username'],
                'role'        => $role ? $role['name'] : 'Customer',
                'branch_id'   => $user['branch_id'],
                'branch_name' => $branch['name'] ?? null,
                'isLoggedIn'  => true,
            ]);

            AuditService::log('Auth', 'LOGIN', (string) $user['id'], 'Successful login');

            return redirect()->to('/dashboard');
        }

        LoginThrottle::fail((string) $username, $ip);

        return redirect()->back()->with('error', 'Invalid username or password');
    }

    public function signup()
    {
        $roleModel = new RoleModel();
        $data['roles'] = $roleModel->findAll();
        $data['branches'] = (new BranchModel())->orderBy('name', 'ASC')->findAll();
        return view('auth/signup', $data);
    }

    public function signupSubmit()
    {
        $userModel = new UserModel();

        $rules = [
            'username'  => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'password'  => 'required|min_length[8]',
            'branch_id' => 'required|is_not_unique[branches.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Public signup is ALWAYS a Customer. Never trust a role from the form.
        $role = (new RoleModel())->where('name', 'Customer')->first();
        if (!$role) {
            return redirect()->back()->with('errors', ['role' => 'Customer role not found. Run RoleSeeder.']);
        }

        $branchId = (int) $this->request->getPost('branch_id');

        $userModel->insert([
            'username'      => $this->request->getPost('username'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'       => $role['id'],
            'branch_id'     => $branchId,
        ]);
        $userId = $userModel->getInsertID();

        $customerCode = 'C' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT);
        $customerModel = new CustomerModel();
        $customerModel->insert([
            'user_id'     => $userId,
            'branch_id'   => $branchId,
            'customer_id' => $customerCode,
            'full_name'   => $this->request->getPost('username'),
            'kyc_status'  => 'Pending',
        ]);
        (new AccountModel())->ensureSavingsAccount(
            (int) $customerModel->getInsertID(),
            $branchId,
            'SAV-' . $customerCode
        );

        return redirect()->to('/login')->with('success', 'Registration successful. Please log in.');
    }

    public function logout()
    {
        AuditService::log('Auth', 'LOGOUT', (string) session()->get('user_id'), 'User logged out');
        session()->destroy();
        return redirect()->to('/login');
    }
}