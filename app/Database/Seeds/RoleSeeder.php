<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'SUPER ADMIN', 'description' => 'System-wide access'],
            ['name' => 'Branch Manager', 'description' => 'Approve loans, Approve ledger adjustments'],
            ['name' => 'Loan Officer', 'description' => 'Create loan applications, Review documents, Recommend loans'],
            ['name' => 'Account Officer', 'description' => 'Manage customer accounts and profiles'],
            ['name' => 'Teller', 'description' => 'Create deposits, Create withdrawals, View customer balances'],
            ['name' => 'Auditor', 'description' => 'View transactions, View audit logs (Cannot modify financial records)'],
            ['name' => 'Customer', 'description' => 'Standard user account for bank customers'],
        ];

        foreach ($data as $role) {
            $existing = $this->db->table('roles')->where('name', $role['name'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('roles')->where('id', $existing['id'])->update([
                    'description' => $role['description'],
                ]);
                continue;
            }

            $this->db->table('roles')->insert($role);
        }
    }
}
