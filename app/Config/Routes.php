<?php

use CodeIgniter\Router\RouteCollection;

// Web Routes
$routes->get('/', 'Home::index');

// Authentication Web Routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::loginSubmit');
$routes->get('/signup', 'AuthController::signup');
$routes->post('/signup', 'AuthController::signupSubmit');
$routes->get('/logout', 'AuthController::logout');

// Protected Web Routes
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->group('audit', ['filter' => ['auth', 'role:SUPER ADMIN,Auditor']], static function ($routes) {
    $routes->get('/', 'AuditController::index');
});

$routes->group('reconciliation', ['filter' => ['auth', 'role:SUPER ADMIN,Auditor']], static function ($routes) {
    $routes->get('/', 'ReconciliationController::index');
});

$routes->group('reports', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'ReportController::index');
    $routes->get('transactions', 'ReportController::dailyTransactions');
    $routes->get('deposits', 'ReportController::deposits');
    $routes->get('withdrawals', 'ReportController::withdrawals');
    $routes->get('loans', 'ReportController::loans');
    $routes->get('teller', 'ReportController::tellerSummary');
});

$routes->group('branches', ['filter' => ['auth', 'role:SUPER ADMIN,Branch Manager']], static function ($routes) {
    $routes->get('/', 'BranchController::index');
    $routes->post('/', 'BranchController::create');
    $routes->get('(:num)', 'BranchController::show/$1');
    $routes->post('(:num)/assign', 'BranchController::assignEmployee/$1');
});

$routes->group('employees', ['filter' => ['auth', 'role:SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'EmployeeController::index');
    $routes->post('create', 'EmployeeController::create');
    $routes->post('update/(:num)', 'EmployeeController::update/$1');
});

$routes->group('ledger', ['filter' => 'auth'], static function ($routes) {
    $routes->group('', ['filter' => 'role:Teller,Account Officer,Branch Manager,SUPER ADMIN'], static function ($routes) {
        $routes->get('post', 'LedgerController::create');
        $routes->post('post', 'LedgerController::store');
    });

    $routes->group('', ['filter' => 'role:Teller,Account Officer,Branch Manager,SUPER ADMIN,Auditor'], static function ($routes) {
        $routes->get('/', 'LedgerController::index');
        $routes->get('chart', 'LedgerController::chart');
        $routes->get('(:num)', 'LedgerController::show/$1');
    });
});

$routes->group('transfers', ['filter' => ['auth', 'role:Teller,Account Officer,Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'TransferController::index');
    $routes->post('/', 'TransferController::store');
});

// Loan Products Web Routes (Branch Manager & Super Admin)
$routes->group('loans/products', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'LoanProductController::index');
    $routes->post('create', 'LoanProductController::create');
    $routes->post('update/(:num)', 'LoanProductController::update/$1');
    $routes->get('toggle/(:num)', 'LoanProductController::toggle/$1');
    $routes->get('delete/(:num)', 'LoanProductController::delete/$1');
});

// Loan Applications Web Routes (Loan Officer, Branch Manager, Super Admin)
$routes->group('loans/applications', ['filter' => ['auth', 'role:Loan Officer,Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'LoanApplicationController::index');
    $routes->get('view/(:num)', 'LoanApplicationController::view/$1');
});
$routes->group('loans/apply', ['filter' => ['auth', 'role:Loan Officer,Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'LoanApplicationController::apply');
    $routes->post('/', 'LoanApplicationController::submit');
});

// Loan Approval & Disbursement Actions (Branch Manager & Super Admin)
$routes->post('loans/applications/approve/(:num)', 'LoanApprovalController::approve/$1', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']]);
$routes->post('loans/applications/reject/(:num)', 'LoanApprovalController::reject/$1', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']]);
$routes->get('loans/active', 'LoanApprovalController::activeLoans', ['filter' => 'auth']);
$routes->get('loans/schedule/(:num)', 'LoanApprovalController::schedule/$1', ['filter' => 'auth']);



// FD Web Pages: Branch Manager / Super Admin
$routes->group('fd', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('products', 'FdPageController::products');
    $routes->post('products/save', 'FdPageController::productSave');
    $routes->post('products/save/(:num)', 'FdPageController::productSave/$1');
    $routes->post('products/toggle/(:num)', 'FdPageController::productToggle/$1');
    $routes->get('interest-logs', 'FdPageController::logs');
});

// FD Web Pages: Teller / Super Admin
$routes->group('fd', ['filter' => ['auth', 'role:Teller,SUPER ADMIN']], static function ($routes) {
    $routes->get('open', 'FdPageController::open');
    $routes->post('open', 'FdPageController::openSubmit');
    $routes->get('certificate/(:num)', 'FdPageController::certificate/$1');
});

// FD Calculator: Branch Manager / Teller / Super Admin
$routes->get('fd/calculator', 'FdPageController::calculator', ['filter' => ['auth', 'role:Branch Manager,Teller,SUPER ADMIN']]);
$routes->post('fd/calculator', 'FdPageController::calculator', ['filter' => ['auth', 'role:Branch Manager,Teller,SUPER ADMIN']]);

// Phase 1: Customer Management Web Routes (Account Officer, Branch Manager, Super Admin, Teller)
$routes->group('customers', ['filter' => ['auth', 'role:Account Officer,Branch Manager,SUPER ADMIN,Teller']], static function ($routes) {
    $routes->get('/', 'CustomerController::index');
    $routes->get('create', 'CustomerController::create');
    $routes->post('create', 'CustomerController::createSubmit');
    $routes->get('view/(:num)', 'CustomerController::view/$1');
    $routes->get('edit/(:num)', 'CustomerController::edit/$1');
    $routes->post('edit/(:num)', 'CustomerController::editSubmit/$1');
    $routes->post('kyc/(:num)', 'CustomerController::kycSubmit/$1');
});

// Phase 1: Bank Accounts Web Routes (Account Officer, Teller, Branch Manager, Super Admin)
$routes->group('accounts', ['filter' => ['auth', 'role:Account Officer,Teller,Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'AccountController::index');
    $routes->get('open', 'AccountController::open');
    $routes->post('open', 'AccountController::openSubmit');
    $routes->get('view/(:num)', 'AccountController::view/$1');
    $routes->post('toggle-status/(:num)', 'AccountController::toggleStatus/$1');
});

// Phase 2: Deposits Web Routes (Teller, Branch Manager, Super Admin)
$routes->group('deposits', ['filter' => ['auth', 'role:Teller,Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'DepositController::index');
    $routes->get('create', 'DepositController::create');
    $routes->post('create', 'DepositController::createSubmit');
    $routes->get('receipt/(:num)', 'DepositController::receipt/$1');
});

// Phase 2: Withdrawals Web Routes (Teller, Branch Manager, Super Admin)
$routes->group('withdrawals', ['filter' => ['auth', 'role:Teller,Branch Manager,SUPER ADMIN']], static function ($routes) {
    $routes->get('/', 'WithdrawalController::index');
    $routes->get('create', 'WithdrawalController::create');
    $routes->post('create', 'WithdrawalController::createSubmit');
    $routes->get('receipt/(:num)', 'WithdrawalController::receipt/$1');
});

// General Operations & Lookup APIs
$routes->get('api/customers/search', 'Api\CustomerApi::search', ['filter' => 'auth']);
$routes->get('api/customers/(:num)', 'Api\CustomerApi::get/$1', ['filter' => 'auth']);
$routes->post('api/customers/kyc/(:num)', 'Api\CustomerApi::updateKyc/$1', ['filter' => ['auth', 'role:Account Officer,Branch Manager,SUPER ADMIN']]);

$routes->get('api/accounts/lookup/(:segment)', 'Api\AccountApi::lookup/$1', ['filter' => 'auth']);
$routes->get('api/accounts/by-customer/(:num)', 'Api\AccountApi::byCustomer/$1', ['filter' => 'auth']);
$routes->post('api/accounts/open', 'Api\AccountApi::open', ['filter' => ['auth', 'role:Account Officer,Branch Manager,SUPER ADMIN']]);

$routes->post('api/operations/deposit', 'Api\TransactionOperationApi::deposit', ['filter' => ['auth', 'role:Teller,Branch Manager,SUPER ADMIN']]);
$routes->post('api/operations/withdraw', 'Api\TransactionOperationApi::withdraw', ['filter' => ['auth', 'role:Teller,Branch Manager,SUPER ADMIN']]);

$routes->group('api/v1', static function ($routes) {
    $routes->post('auth/login', 'Api\AuthApi::login');
    $routes->post('auth/signup', 'Api\AuthApi::signup');

    // FD Products: Branch Manager / Super Admin only
    $routes->group('fd-products', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']], static function ($routes) {
        $routes->get('/', 'Api\FdProductApi::index');
        $routes->post('/', 'Api\FdProductApi::create');
        $routes->put('(:num)', 'Api\FdProductApi::update/$1');
        $routes->patch('(:num)/toggle', 'Api\FdProductApi::toggle/$1');
    });

    // FD Account Creation (Story 8): Teller / Super Admin only
    $routes->group('fd-accounts', ['filter' => ['auth', 'role:Teller,SUPER ADMIN']], static function ($routes) {
        $routes->post('/', 'Api\FdAccountApi::create');
        $routes->get('(:num)/certificate', 'Api\FdAccountApi::certificate/$1');
    });

    // Loan Products: Branch Manager / Super Admin only
    $routes->group('loan-products', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']], static function ($routes) {
        $routes->get('/', 'Api\LoanProductApi::index');
        $routes->get('(:num)', 'Api\LoanProductApi::show/$1');
        $routes->post('/', 'Api\LoanProductApi::create');
        $routes->put('(:num)', 'Api\LoanProductApi::update/$1');
        $routes->patch('(:num)/toggle', 'Api\LoanProductApi::toggle/$1');
        $routes->delete('(:num)', 'Api\LoanProductApi::delete/$1');
    });

    // Loan Applications API
    $routes->group('loan-applications', ['filter' => ['auth', 'role:Loan Officer,Branch Manager,SUPER ADMIN']], static function ($routes) {
        $routes->get('/', 'Api\LoanApplicationApi::index');
        $routes->get('(:num)', 'Api\LoanApplicationApi::show/$1');
        $routes->post('/', 'Api\LoanApplicationApi::create');
        $routes->post('(:num)/approve', 'Api\LoanApprovalApi::approve/$1', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']]);
        $routes->post('(:num)/reject', 'Api\LoanApprovalApi::reject/$1', ['filter' => ['auth', 'role:Branch Manager,SUPER ADMIN']]);
    });

    // Active Loans & Schedule API
    $routes->get('loans/active', 'Api\LoanApprovalApi::activeLoans', ['filter' => 'auth']);
    $routes->post('loans/preview-emi', 'Api\LoanEmiApi::previewEmi');
    $routes->get('loans/(:num)/schedule', 'Api\LoanEmiApi::getSchedule/$1', ['filter' => 'auth']);
});
