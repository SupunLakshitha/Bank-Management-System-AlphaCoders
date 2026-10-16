<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditController extends BaseController
{
    public function __construct()
    {
        if (!session()->get('isLoggedIn')) {
            redirect()->to(base_url('login'))->send();
            exit;
        }
        if (!in_array(session()->get('role'), ['SUPER ADMIN', 'Auditor'], true)) {
            redirect()->to(base_url('dashboard'))->with('error', 'You do not have permission to access the audit log.')->send();
            exit;
        }
    }

    public function index()
    {
        $module = $this->request->getGet('module');
        $action = $this->request->getGet('action');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $model = new AuditLogModel();

        return view('audit/index', [
            'logs' => $model->getLogsFiltered($module, $action, $startDate, $endDate),
            'filters' => ['module' => $module, 'action' => $action, 'start_date' => $startDate, 'end_date' => $endDate],
            'modules' => ['Auth', 'Accounts', 'Deposits', 'Withdrawals', 'Loans'],
            'actions' => ['LOGIN', 'LOGOUT', 'ACCOUNT_OPEN', 'DEPOSIT', 'WITHDRAWAL', 'LOAN_APPROVAL'],
        ]);
    }
}
