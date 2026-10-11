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




// API Routes
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