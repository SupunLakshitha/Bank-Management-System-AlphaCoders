<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\BranchModel;
use App\Models\CustomerModel;
use App\Models\AccountModel;

class AuthApi extends BaseController
{
    public function login()
    {
        $userModel = new UserModel();
        
        // For API, get JSON input
        $json = $this->request->getJSON();
        if (!$json || !isset($json->username) || !isset($json->password)) {
            return $this->response->setJSON(['error' => 'Missing username or password'])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $user = $userModel->where('username', $json->username)->first();

        if ($user && password_verify($json->password, $user['password_hash'])) {
            $roleModel = new RoleModel();
            $role = $roleModel->find($user['role_id']);
            $branch = ! empty($user['branch_id']) ? (new BranchModel())->find($user['branch_id']) : null;

            // For simplicity, we are setting session. In a real stateless API, return a JWT token.
            session()->set([
                'user_id'     => $user['id'],
                'username'    => $user['username'],
                'role'        => $role ? $role['name'] : 'Customer',
                'branch_id'   => $user['branch_id'],
                'branch_name' => $branch['name'] ?? null,
                'isLoggedIn'  => true,
            ]);

            return $this->response->setJSON([
                'message' => 'Login successful',
                'user'    => [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'role'     => $role ? $role['name'] : 'Customer',
                    'branch'   => $branch['name'] ?? null,
                ]
            ]);
        }

        return $this->response->setJSON(['error' => 'Invalid username or password'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }

    public function signup()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();

        $json = $this->request->getJSON(true); // get as array

        if (!$json) {
            return $this->response->setJSON(['error' => 'Invalid JSON input'])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Temporary set $_POST for validation to work easily
        $this->request->setGlobal('post', $json);

        $rules = [
            'username'  => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'password'  => 'required|min_length[6]',
            'branch_id' => 'required|is_not_unique[branches.id]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['errors' => $this->validator->getErrors()])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $customerRole = $roleModel->where('name', 'Customer')->first();
        $branchId = (int) $json['branch_id'];

        $userModel->insert([
            'username'      => $json['username'],
            'password_hash' => password_hash($json['password'], PASSWORD_DEFAULT),
            'role_id'       => $customerRole['id'],
            'branch_id'     => $branchId,
        ]);
        $userId = $userModel->getInsertID();
        $customerCode = 'C' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT);

        $customerModel = new CustomerModel();
        $customerModel->insert([
            'user_id'     => $userId,
            'branch_id'   => $branchId,
            'customer_id' => $customerCode,
            'full_name'   => $json['username'],
            'kyc_status'  => 'Pending',
        ]);
        (new AccountModel())->ensureSavingsAccount(
            (int) $customerModel->getInsertID(),
            $branchId,
            'SAV-' . $customerCode
        );

        return $this->response->setJSON(['message' => 'Registration successful'])->setStatusCode(ResponseInterface::HTTP_CREATED);
    }
}
