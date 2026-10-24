<?php

namespace App\Commands;

use App\Libraries\FdInterestCalculator;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AccrueFdInterest extends BaseCommand
{
    protected $group       = 'FD';
    protected $name        = 'fd:accrue';
    protected $description = 'Accrue interest for all active fixed deposits.';

    public function run(array $params)
    {
        $db    = \Config\Database::connect();
        $calc  = new FdInterestCalculator();
        $today = date('Y-m-d');

        $fds = $db->table('fixed_deposits')->where('status', 'Active')->get()->getResultArray();

        foreach ($fds as $fd) {
            $exists = $db->table('interest_logs')
                ->where('fd_id', $fd['id'])
                ->where('accrual_date', $today)
                ->countAllResults();
            if ($exists) {
                continue;
            }

            $total = $calc->accruedInterest(
                (float) $fd['principal'],
                (float) $fd['interest_rate'],
                $fd['start_date'],
                $fd['maturity_date'],
                $today,
                $fd['interest_type'],
                $fd['day_count'],
                (int) $fd['compounding_per_year']
            );

            $delta = round($total - (float) $fd['accrued_interest'], 2);
            if ($delta <= 0) {
                continue;
            }

            $db->transStart();
            $db->table('interest_logs')->insert([
                'fd_id'           => $fd['id'],
                'accrual_date'    => $today,
                'interest_amount' => $delta,
                'total_accrued'   => $total,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
            $db->table('fixed_deposits')->where('id', $fd['id'])->update([
                'accrued_interest' => $total,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
            $db->transComplete();

            CLI::write("FD {$fd['fd_number']}: +{$delta}", 'green');
        }

        CLI::write('Done.', 'yellow');
    }
}