<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name'          => 'Savings Account',
                'code'          => 'SAVINGS',
                'interest_rate' => 5.00,
                'min_balance'   => 1000.00,
                'description'   => 'Standard interest-earning savings account with easy deposits and withdrawals.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Current Account',
                'code'          => 'CURRENT',
                'interest_rate' => 0.00,
                'min_balance'   => 5000.00,
                'description'   => 'Transactional checking account designed for everyday business and personal payments.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Business Account',
                'code'          => 'BUSINESS',
                'interest_rate' => 3.00,
                'min_balance'   => 25000.00,
                'description'   => 'Commercial banking account tailored for corporate and retail merchant operations.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Fixed Deposit Source Account',
                'code'          => 'FD_SAVINGS',
                'interest_rate' => 6.50,
                'min_balance'   => 5000.00,
                'description'   => 'High-yield savings account tied to term investments and certificate roll-overs.',
                'is_active'     => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($types as $t) {
            $existing = $this->db->table('account_types')->where('code', $t['code'])->get()->getRowArray();
            if (!$existing) {
                $this->db->table('account_types')->insert($t);
            }
        }
    }
}
