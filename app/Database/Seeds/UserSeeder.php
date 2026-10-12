<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['username' => 'admin',           'password' => 'admin123',          'role' => 'SUPER ADMIN'],
            ['username' => 'branch-manager',  'password' => 'branch-manager123', 'role' => 'Branch Manager'],
            ['username' => 'loan-officer',    'password' => 'loan-officer123',   'role' => 'Loan Officer'],
            ['username' => 'account-officer', 'password' => 'account-officer123','role' => 'Account Officer'],
            ['username' => 'teller',          'password' => 'teller123',         'role' => 'Teller'],
            ['username' => 'auditor',         'password' => 'auditor123',        'role' => 'Auditor'],
            ['username' => 'customer',        'password' => 'customer123',       'role' => 'Customer'],
        ];

        // Clean up any rogue username with invisible characters first
        $this->db->query("UPDATE users SET username = 'branch-manager' WHERE username LIKE 'branch-manager%'");

        foreach ($users as $u) {
            $role = $this->db->table('roles')->where('name', $u['role'])->get()->getRowArray();
            $roleId = $role ? $role['id'] : null;

            $existing = $this->db->table('users')->where('username', $u['username'])->get()->getRowArray();

            $data = [
                'username'      => $u['username'],
                'password_hash' => password_hash($u['password'], PASSWORD_DEFAULT),
                'role_id'       => $roleId,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $this->db->table('users')->where('id', $existing['id'])->update($data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('users')->insert($data);
            }
        }
    }
}
