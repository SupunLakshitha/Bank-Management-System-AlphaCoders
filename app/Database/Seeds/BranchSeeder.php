<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            ['name' => 'Colombo Main Branch', 'code' => 'COL01', 'location' => 'No. 100, York Street, Colombo 01'],
            ['name' => 'Kandy Branch',        'code' => 'KDY01', 'location' => 'No. 45, Dalada Veediya, Kandy'],
            ['name' => 'Kegalle Branch',      'code' => 'KEG01', 'location' => 'No. 22, Main Street, Kegalle'],
            ['name' => 'Galle Branch',        'code' => 'GAL01', 'location' => 'No. 88, Fort Road, Galle'],
        ];

        foreach ($branches as $b) {
            $existing = $this->db->table('branches')->where('code', $b['code'])->get()->getRowArray();
            if (!$existing) {
                $this->db->table('branches')->insert($b);
            }
        }
    }
}
