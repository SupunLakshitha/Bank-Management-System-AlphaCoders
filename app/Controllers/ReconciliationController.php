<?php

namespace App\Controllers;

use App\Services\ReconciliationService;

class ReconciliationController extends BaseController
{
    public function __construct()
    {
        if (!session()->get('isLoggedIn')) {
            redirect()->to(base_url('login'))->send();
            exit;
        }
        if (!in_array(session()->get('role'), ['SUPER ADMIN', 'Auditor'], true)) {
            redirect()->to(base_url('dashboard'))->with('error', 'You do not have permission to access reconciliation.')->send();
            exit;
        }
    }

    public function index()
    {
        $service = new ReconciliationService();
        $accounts = $service->reconcileAccounts();
        $statusFilter = $this->request->getGet('status') ?? 'all';
        $balancedCount = count(array_filter($accounts, static fn ($row) => $row['reconciliation_status'] === 'BALANCED'));
        $discrepancyCount = count($accounts) - $balancedCount;
        $visibleAccounts = $statusFilter === 'discrepancy'
            ? array_values(array_filter($accounts, static fn ($row) => $row['reconciliation_status'] === 'DISCREPANCY'))
            : $accounts;

        return view('reconciliation/index', [
            'accounts' => $visibleAccounts,
            'statusFilter' => $statusFilter,
            'totalAccounts' => count($accounts),
            'balancedCount' => $balancedCount,
            'discrepancyCount' => $discrepancyCount,
            'ledger' => $service->checkLedgerIntegrity(),
        ]);
    }
}
