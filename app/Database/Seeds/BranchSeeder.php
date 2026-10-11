<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            ['name' => 'Colombo Branch', 'code' => 'CMB', 'location' => 'No. 100, Galle Road, Colombo 03'],
            ['name' => 'Kandy Branch', 'code' => 'KAN', 'location' => 'No. 25, Dalada Veediya, Kandy'],
            ['name' => 'Kegalle Branch', 'code' => 'KEG', 'location' => 'No. 12, Main Street, Kegalle'],
            ['name' => 'Galle Branch', 'code' => 'GAL', 'location' => 'No. 48, Church Street, Galle Fort'],
        ];

        foreach ($branches as $branch) {
            $existing = $this->db->table('branches')->where('code', $branch['code'])->get()->getRowArray();
            if ($existing) {
                $this->db->table('branches')->where('id', $existing['id'])->update([
                    'name'     => $branch['name'],
                    'location' => $branch['location'],
                ]);
                continue;
            }

            $this->db->table('branches')->insert($branch);
        }

        $this->removeUnusedDuplicateColombo();
    }

    private function removeUnusedDuplicateColombo(): void
    {
        $duplicate = $this->db->table('branches')->where('code', 'COL')->get()->getRowArray();
        if (! $duplicate) {
            return;
        }

        $id = (int) $duplicate['id'];
        $tables = [
            'users',
            'customers',
            'accounts',
            'transactions',
            'loans',
            'loan_applications',
            'fixed_deposits',
        ];

        foreach ($tables as $table) {
            if (! $this->db->tableExists($table) || ! $this->db->fieldExists('branch_id', $table)) {
                continue;
            }
            $count = $this->db->table($table)->where('branch_id', $id)->countAllResults();
            if ($count > 0) {
                return;
            }
        }

        $this->db->table('branches')->where('id', $id)->delete();
    }
}
