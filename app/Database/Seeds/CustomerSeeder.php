<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $customerRole = $this->db->table('roles')->where('name', 'Customer')->get()->getRowArray();
        $roleId = $customerRole ? $customerRole['id'] : 7;

        $customers = [
            [
                'username'     => 'customer',
                'customer_id'  => 'C000',
                'full_name'    => 'Demo Customer',
                'nic'          => '200012345678',
                'dob'          => '2000-01-15',
                'phone'        => '0770000000',
                'address'      => 'No. 1, Main Street, Colombo 01',
                'occupation'   => 'Customer',
                'kyc_status'   => 'Verified',
                'branch_code'  => 'CMB',
                'opening'      => 50000.00,
            ],
            [
                'username'     => 'customer_kamal',
                'customer_id'  => 'C001',
                'full_name'    => 'Kamal Perera',
                'nic'          => '198512345678',
                'dob'          => '1985-05-14',
                'phone'        => '0771234567',
                'address'      => 'No. 18, Peradeniya Road, Kandy',
                'occupation'   => 'Senior Software Engineer',
                'kyc_status'   => 'Verified',
                'branch_code'  => 'KAN',
                'opening'      => 75000.00,
            ],
            [
                'username'     => 'customer_nimal',
                'customer_id'  => 'C002',
                'full_name'    => 'Nimal Silva',
                'nic'          => '199087654321',
                'dob'          => '1990-11-20',
                'phone'        => '0719876543',
                'address'      => 'No. 7, Main Street, Kegalle',
                'occupation'   => 'Business Owner / Retail Trader',
                'kyc_status'   => 'Verified',
                'branch_code'  => 'KEG',
                'opening'      => 32000.00,
            ],
            [
                'username'     => 'customer_sunil',
                'customer_id'  => 'C003',
                'full_name'    => 'Sunil Shantha',
                'nic'          => '197823456789',
                'dob'          => '1978-03-08',
                'phone'        => '0763456789',
                'address'      => 'No. 88, Church Street, Galle',
                'occupation'   => 'Civil Contractor',
                'kyc_status'   => 'Verified',
                'branch_code'  => 'GAL',
                'opening'      => 18000.00,
            ],
        ];

        foreach ($customers as $c) {
            $branch = $this->db->table('branches')->where('code', $c['branch_code'])->get()->getRowArray();
            if (! $branch) {
                continue;
            }

            $user = $this->db->table('users')->where('username', $c['username'])->get()->getRowArray();
            $userData = [
                'password_hash' => password_hash('customer123', PASSWORD_DEFAULT),
                'role_id'       => $roleId,
                'branch_id'     => $branch['id'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            if (! $user) {
                $userData['username']   = $c['username'];
                $userData['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('users')->insert($userData);
                $userId = $this->db->insertID();
            } else {
                $this->db->table('users')->where('id', $user['id'])->update($userData);
                $userId = $user['id'];
            }

            $existingCustomer = $this->db->table('customers')->where('customer_id', $c['customer_id'])->get()->getRowArray();
            $customerData = [
                'user_id'     => $userId,
                'branch_id'   => $branch['id'],
                'customer_id' => $c['customer_id'],
                'full_name'   => $c['full_name'],
                'nic'         => $c['nic'],
                'dob'         => $c['dob'],
                'phone'       => $c['phone'],
                'address'     => $c['address'],
                'occupation'  => $c['occupation'],
                'kyc_status'  => $c['kyc_status'],
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            if ($existingCustomer) {
                $this->db->table('customers')->where('id', $existingCustomer['id'])->update($customerData);
                $customerRowId = $existingCustomer['id'];
            } else {
                $customerData['created_at'] = date('Y-m-d H:i:s');
                $this->db->table('customers')->insert($customerData);
                $customerRowId = $this->db->insertID();
            }

            $this->db->table('accounts')->where('customer_id', $customerRowId)->update([
                'branch_id'  => $branch['id'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $accounts = $this->db->table('accounts')->where('customer_id', $customerRowId)->get()->getResultArray();
            $savings  = null;
            foreach ($accounts as $account) {
                $this->db->table('transactions')->where('account_id', $account['id'])->update([
                    'branch_id' => $branch['id'],
                ]);
                if ($account['account_type'] === 'SAVINGS' && $savings === null) {
                    $savings = $account;
                }
            }

            if (! $savings) {
                $gl = $this->db->table('gl_accounts')->where('code', 'GL-20100')->get()->getRowArray();
                $this->db->table('accounts')->insert([
                    'account_no'    => 'SA-' . $c['customer_id'],
                    'customer_id'   => $customerRowId,
                    'branch_id'     => $branch['id'],
                    'gl_account_id' => $gl['id'],
                    'account_type'  => 'SAVINGS',
                    'currency'      => 'LKR',
                    'balance'       => $c['opening'],
                    'status'        => 'Active',
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
                $accountId = $this->db->insertID();

                $this->db->table('transactions')->insert([
                    'tx_id'       => 'TX-OPEN-' . $c['customer_id'],
                    'account_id'  => $accountId,
                    'type'        => 'DEPOSIT',
                    'amount'      => $c['opening'],
                    'tx_date'     => date('Y-m-d'),
                    'description' => 'Opening balance at ' . $branch['name'],
                    'branch_id'   => $branch['id'],
                    'status'      => 'POSTED',
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
