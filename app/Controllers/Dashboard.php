<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $metrics = [
            'total_customers'        => 0,
            'active_accounts'        => 0,
            'total_deposit_balance'  => 0.00,
            'today_deposits'         => 0.00,
            'today_withdrawals'      => 0.00,
            'total_loans'            => 0.00,
            'active_loans'           => 0,
            'outstanding_exposure'   => 0.00,
            'overdue_loans_count'    => 0,
        ];

        if ($db->tableExists('customers')) {
            $metrics['total_customers'] = (int) $db->table('customers')->countAllResults();
        }

        if ($db->tableExists('accounts')) {
            $metrics['active_accounts'] = (int) $db->table('accounts')->where('status', 'active')->countAllResults();
            $metrics['total_deposit_balance'] = (float) ($db->table('accounts')->selectSum('balance')->get()->getRow()->balance ?? 0);
        }

        if ($db->tableExists('deposits')) {
            $metrics['today_deposits'] = (float) ($db->table('deposits')->selectSum('amount')->where('DATE(created_at) = CURDATE()', null, false)->get()->getRow()->amount ?? 0);
        }

        if ($db->tableExists('withdrawals')) {
            $metrics['today_withdrawals'] = (float) ($db->table('withdrawals')->selectSum('amount')->where('DATE(created_at) = CURDATE()', null, false)->get()->getRow()->amount ?? 0);
        }

        if ($db->tableExists('loans')) {
            $loanAmountColumn = $db->fieldExists('loan_amount', 'loans') ? 'loan_amount' : 'principal_amount';
            if ($db->fieldExists($loanAmountColumn, 'loans')) {
                $metrics['total_loans'] = (float) ($db->table('loans')->selectSum($loanAmountColumn)->get()->getRow()->{$loanAmountColumn} ?? 0);
            }
            if ($db->fieldExists('status', 'loans')) {
                $metrics['active_loans'] = (int) $db->table('loans')->whereIn('status', ['active', 'Active'])->countAllResults();
            }
            if ($db->fieldExists('outstanding_balance', 'loans')) {
                $metrics['outstanding_exposure'] = (float) ($db->table('loans')->selectSum('outstanding_balance')->get()->getRow()->outstanding_balance ?? 0);
            }
        }

        if ($db->tableExists('loan_repayment_schedules')) {
            $metrics['overdue_loans_count'] = (int) $db->table('loan_repayment_schedules')
                ->groupStart()
                    ->where('status', 'Overdue')
                    ->orGroupStart()
                        ->where('status', 'Unpaid')
                        ->where('due_date <', date('Y-m-d'))
                    ->groupEnd()
                ->groupEnd()
                ->countAllResults();
        } elseif ($db->tableExists('loans') && $db->fieldExists('status', 'loans')) {
            $metrics['overdue_loans_count'] = (int) $db->table('loans')->where('status', 'Defaulted')->countAllResults();
        }

        $data = [
            'username'    => session()->get('username'),
            'role'        => session()->get('role'),
            'branch_name' => session()->get('branch_name'),
            'metrics'     => $metrics,
        ];
        return view('dashboard/index', $data);
    }
}
