<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run()
    {
        // Get customer Kamal (C001) and Nimal (C002)
        $kamal = $this->db->table('customers')->where('customer_id', 'C001')->get()->getRowArray();
        $nimal = $this->db->table('customers')->where('customer_id', 'C002')->get()->getRowArray();
        $sunil = $this->db->table('customers')->where('customer_id', 'C003')->get()->getRowArray();

        $savingsType = $this->db->table('account_types')->where('code', 'SAVINGS')->get()->getRowArray();
        $currentType = $this->db->table('account_types')->where('code', 'CURRENT')->get()->getRowArray();

        $branch = $this->db->table('branches')->get()->getRowArray();
        $branchId = $branch ? $branch['id'] : null;

        $accounts = [];

        if ($kamal && $savingsType) {
            $accounts[] = [
                'account_no'      => '1000012345',
                'customer_id'     => $kamal['id'],
                'account_type_id' => $savingsType['id'],
                'branch_id'       => $branchId,
                'balance'         => 150000.00,
                'interest_rate'   => 5.00,
                'status'          => 'Active',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
        }

        if ($nimal && $savingsType) {
            $accounts[] = [
                'account_no'      => '1000012346',
                'customer_id'     => $nimal['id'],
                'account_type_id' => $savingsType['id'],
                'branch_id'       => $branchId,
                'balance'         => 85000.00,
                'interest_rate'   => 5.00,
                'status'          => 'Active',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
        }

        if ($sunil && $currentType) {
            $accounts[] = [
                'account_no'      => '1000012347',
                'customer_id'     => $sunil['id'],
                'account_type_id' => $currentType['id'],
                'branch_id'       => $branchId,
                'balance'         => 240000.00,
                'interest_rate'   => 0.00,
                'status'          => 'Active',
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
        }

        foreach ($accounts as $acc) {
            $existing = $this->db->table('accounts')->where('account_no', $acc['account_no'])->get()->getRowArray();
            if (!$existing) {
                $this->db->table('accounts')->insert($acc);
            }
        }
    }
}
