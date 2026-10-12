<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            ['username' => 'admin', 'password' => 'admin123', 'role' => 'SUPER ADMIN', 'branch' => 'CMB'],
            ['username' => 'branch-manager', 'password' => 'branch-manager123', 'role' => 'Branch Manager', 'branch' => 'CMB'],
            ['username' => 'loan-officer', 'password' => 'loan-officer123', 'role' => 'Loan Officer', 'branch' => 'KAN'],
            ['username' => 'account-officer', 'password' => 'account-officer123', 'role' => 'Account Officer', 'branch' => 'KEG'],
            ['username' => 'teller', 'password' => 'teller123', 'role' => 'Teller', 'branch' => 'GAL'],
            ['username' => 'auditor', 'password' => 'auditor123', 'role' => 'Auditor', 'branch' => 'CMB'],
        ];

        foreach ($accounts as $account) {
            $role = $this->db->table('roles')->where('name', $account['role'])->get()->getRowArray();
            $branch = $this->db->table('branches')->where('code', $account['branch'])->get()->getRowArray();
            if (!$role || !$branch) {
                continue;
            }

            $data = [
                'password_hash' => password_hash($account['password'], PASSWORD_DEFAULT),
                'role_id'       => $role['id'],
                'branch_id'     => $branch['id'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            $existing = $this->db->table('users')->where('username', $account['username'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('users')->where('id', $existing['id'])->update($data);
                continue;
            }

            $data['username']   = $account['username'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('users')->insert($data);
        }
    }
}
