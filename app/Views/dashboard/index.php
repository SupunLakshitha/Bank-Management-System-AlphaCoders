<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            background: #f4f6f9;
            color: #333;
        }

        .navbar {
            background: #2a2a40;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .navbar h2 { font-weight: 600; font-size: 20px; color: #4facfe; }

        .btn-logout {
            padding: 8px 15px;
            background: rgba(255, 77, 79, 0.1);
            border: 1px solid #ff4d4f;
            border-radius: 5px;
            color: #ff4d4f;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #ff4d4f;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .welcome-card h1 { margin-bottom: 10px; color: #1e1e2f; }
        .welcome-card p { color: #666; font-size: 16px; }
        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            background: #e6f7ff;
            border: 1px solid #91d5ff;
            color: #096dd9;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error { background: rgba(255, 77, 79, 0.1); border: 1px solid #ff4d4f; color: #ff4d4f; }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>BMS Dashboard</h2>
        <div>
            <a href="<?= base_url('reports') ?>" class="btn-logout" style="margin-right:10px;border-color:#4facfe;color:#4facfe;">Reports</a>
            <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="welcome-card">
            <h1>Welcome, <?= esc($username) ?>!</h1>
            <p>You have successfully logged into the Bank Management System.</p>
            <div class="role-badge">Role: <?= esc($role) ?></div>
            <?php if (!empty($branch_name)): ?>
                <div class="role-badge">Branch: <?= esc($branch_name) ?></div>
            <?php endif; ?>
        </div>

        <section class="row g-3 mb-4" aria-label="Banking metrics">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h6 text-secondary">Customers &amp; Active Accounts</h2>
                    <div class="fs-4 fw-semibold"><?= number_format($metrics['total_customers'] ?? 0) ?></div>
                    <div class="small text-secondary">Active accounts: <?= number_format($metrics['active_accounts'] ?? 0) ?></div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h6 text-secondary">Total Deposited Funds</h2>
                    <div class="fs-4 fw-semibold">Rs. <?= number_format($metrics['total_deposit_balance'] ?? 0, 2) ?></div>
                    <div class="small text-secondary">Across all accounts</div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h6 text-secondary">Today's Deposits &amp; Withdrawals</h2>
                    <div class="small">Deposits: <strong>Rs. <?= number_format($metrics['today_deposits'] ?? 0, 2) ?></strong></div>
                    <div class="small">Withdrawals: <strong>Rs. <?= number_format($metrics['today_withdrawals'] ?? 0, 2) ?></strong></div>
                </div></div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                    <h2 class="h6 text-secondary">Active Loans &amp; Exposure</h2>
                    <div class="fs-4 fw-semibold"><?= number_format($metrics['active_loans'] ?? 0) ?> active</div>
                    <div class="small">Outstanding: <strong>Rs. <?= number_format($metrics['outstanding_exposure'] ?? 0, 2) ?></strong></div>
                    <div class="small text-secondary">Overdue schedules: <?= number_format($metrics['overdue_loans_count'] ?? 0) ?></div>
                </div></div>
            </div>
        </section>

        <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager', 'Account Officer', 'Teller'])): ?>
            <!-- Customer Management & Bank Accounts -->
            <h3 style="margin-top:28px;color:#1e1e2f;">Customers &amp; Accounts</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-top: 14px;">
                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #4361ee; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Customer Management &amp; KYC</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">View customer directory, manage KYC verifications, profile demographics, and associated facilities.</p>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="<?= base_url('customers') ?>" style="display: inline-block; padding: 8px 14px; background: #4361ee; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Customers Directory &rarr;</a>
                        <a href="<?= base_url('customers/create') ?>" style="display: inline-block; padding: 8px 14px; background: #e0e7ff; color: #4338ca; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">+ Onboard</a>
                    </div>
                </div>

                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #0ea5e9; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Bank Accounts</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Open savings, current &amp; business accounts, inspect available balances, and view transaction statements.</p>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="<?= base_url('accounts') ?>" style="display: inline-block; padding: 8px 14px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">View Accounts &rarr;</a>
                        <a href="<?= base_url('accounts/open') ?>" style="display: inline-block; padding: 8px 14px; background: #e0f2fe; color: #0369a1; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">+ Open Account</a>
                    </div>
                </div>
            </div>

            <!-- Deposits & Withdrawals -->
            <h3 style="margin-top:28px;color:#1e1e2f;">Deposits &amp; Withdrawals (Teller Operations)</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-top: 14px;">
                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #10b981; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Deposit Operations</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Process cash, cheque and online deposits with real-time balance credit and printable receipts.</p>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="<?= base_url('deposits/create') ?>" style="display: inline-block; padding: 8px 14px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Deposit Terminal &rarr;</a>
                        <a href="<?= base_url('deposits') ?>" style="display: inline-block; padding: 8px 14px; background: #ecfdf5; color: #065f46; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Deposits Log</a>
                    </div>
                </div>

                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #ef4444; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Withdrawal Operations</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Debit accounts with strict available balance and minimum balance enforcement, and issue vouchers.</p>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <a href="<?= base_url('withdrawals/create') ?>" style="display: inline-block; padding: 8px 14px; background: #ef4444; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Withdrawal Terminal &rarr;</a>
                        <a href="<?= base_url('withdrawals') ?>" style="display: inline-block; padding: 8px 14px; background: #fef2f2; color: #991b1b; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Withdrawals Log</a>
                    </div>
                </div>
            </div>
        <?php
            $canPostLedger = in_array($role, ['SUPER ADMIN', 'Branch Manager', 'Account Officer', 'Teller'], true);
            $canViewLedger = $canPostLedger || $role === 'Auditor';
        ?>
        <?php if ($canViewLedger): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 24px;">
                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #0f172a;">
                    <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Transaction ledger</h3>
                    <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Deposits, withdrawals, loan movements, interest, fees, and adjustments are posted as balanced ledger entries.</p>
                    <a href="<?= base_url('ledger') ?>" style="display: inline-block; padding: 8px 16px; background: #0f172a; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Open ledger &rarr;</a>
                </div>
                <?php if ($canPostLedger): ?>
                    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #4361ee;">
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Money transfers</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">A transfer debits one account and credits the other in the same ledger posting.</p>
                        <a href="<?= base_url('transfers') ?>" style="display: inline-block; padding: 8px 16px; background: #4361ee; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">New transfer &rarr;</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($role === 'SUPER ADMIN'): ?>
            <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #ef4444; margin-bottom: 20px;">
                <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Employee Management</h3>
                <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Create staff accounts and assign roles.</p>
                <a href="<?= base_url('employees') ?>" style="display: inline-block; padding: 8px 16px; background: #ef4444; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Manage employees &rarr;</a>
            </div>
        <?php endif; ?>

        <?php if ($role !== 'Customer'): ?>
            <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #4facfe; margin-bottom: 20px;">
                <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Branch Management</h3>
                <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Customers, accounts, employees, transactions, and loans are kept with their branch.</p>
                <a href="<?= base_url('branches') ?>" style="display: inline-block; padding: 8px 16px; background: #4facfe; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Open branches &rarr;</a>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager', 'Loan Officer'])): ?>
            <h3 style="margin-top:32px;color:#1e1e2f;">Loans Management</h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 24px;">
                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #4361ee; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Loan Applications Queue</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">View all pending loan applications, credit appraisals, and underwriting statuses.</p>
                    </div>
                    <a href="<?= base_url('loans/applications') ?>" style="display: inline-block; padding: 8px 16px; background: #4361ee; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">View Applications &rarr;</a>
                </div>

                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #10b981; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Submit Loan Application</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Create a multi-step credit application with collateral, guarantors, and KYC uploads.</p>
                    </div>
                    <a href="<?= base_url('loans/apply') ?>" style="display: inline-block; padding: 8px 16px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">Apply for Loan &rarr;</a>
                </div>

                <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager'])): ?>
                    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #7209b7; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Loan Product Engine</h3>
                            <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Configure loan types, interest rates, simple vs compound rules, and limits.</p>
                        </div>
                        <a href="<?= base_url('loans/products') ?>" style="display: inline-block; padding: 8px 16px; background: #7209b7; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">Manage Loan Products &rarr;</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        

                  <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager', 'Teller'])): ?>
            <h3 style="margin-top:32px;color:#1e1e2f;">Fixed Deposits</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 16px;">
                <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager'])): ?>
                    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #f59e0b; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">FD Product Engine</h3>
                            <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Define FD products with interest rates, tenures and minimum amounts.</p>
                        </div>
                        <a href="<?= base_url('fd/products') ?>" style="display: inline-block; padding: 8px 16px; background: #f59e0b; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">Manage FD Products &rarr;</a>
                    </div>
                    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #8b5cf6; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Interest Accrual Logs</h3>
                            <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Audit the daily interest accrual entries for all active FDs.</p>
                        </div>
                        <a href="<?= base_url('fd/interest-logs') ?>" style="display: inline-block; padding: 8px 16px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">View Logs &rarr;</a>
                    </div>
                <?php endif; ?>
                <?php if (in_array($role, ['SUPER ADMIN', 'Teller'])): ?>
                    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #0ea5e9; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Open Fixed Deposit</h3>
                            <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Open an FD for a customer using funds from their savings account.</p>
                        </div>
                        <a href="<?= base_url('fd/open') ?>" style="display: inline-block; padding: 8px 16px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">Open FD &rarr;</a>
                    </div>
                <?php endif; ?>
                <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #14b8a6; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">FD Calculator</h3>
                        <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Preview maturity amount and interest before creating an FD.</p>
                    </div>
                    <a href="<?= base_url('fd/calculator') ?>" style="display: inline-block; padding: 8px 16px; background: #14b8a6; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content;">Open Calculator &rarr;</a>
                </div>
            </div>
        <?php endif; ?>          

        <h3 style="margin-top:32px;color:#1e1e2f;">Reports &amp; Business Intelligence</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:16px;">
            <div style="background:white;padding:24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);border-left:4px solid #4361ee;display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <h3 style="font-size:17px;margin-bottom:8px;color:#1e1e2f;">Bank Reports &amp; Statements</h3>
                    <p style="color:#666;font-size:13px;margin-bottom:16px;">Generate daily transaction ledgers, deposit/withdrawal summaries, loan overdue reports, and cashier/teller performance logs.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="<?= base_url('reports') ?>" style="display:inline-block;padding:8px 14px;background:#4361ee;color:white;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600;">View Reports Hub &rarr;</a>
                    <a href="<?= base_url('reports/transactions') ?>" style="display:inline-block;padding:8px 14px;background:#e0e7ff;color:#4338ca;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600;">Daily Transactions</a>
                    <a href="<?= base_url('reports/loans') ?>" style="display:inline-block;padding:8px 14px;background:#e0e7ff;color:#4338ca;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600;">Loans Report</a>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <?php if (in_array($role, ['SUPER ADMIN', 'Auditor'], true)): ?>
            <h3 style="margin-top:32px;color:#1e1e2f;">Security &amp; Audit</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:16px;">
                <div style="background:white;padding:24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);border-left:4px solid #7209b7;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <h3 style="font-size:17px;margin-bottom:8px;color:#1e1e2f;">Security &amp; Audit Trail</h3>
                        <p style="color:#666;font-size:13px;margin-bottom:16px;">Review login activity and trace deposits, withdrawals, and other recorded banking actions.</p>
                    </div>
                    <a href="<?= base_url('audit') ?>" style="display:inline-block;padding:8px 14px;background:#7209b7;color:white;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600;width:fit-content;">Open Audit Trail &rarr;</a>
                </div>
                <div style="background:white;padding:24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);border-left:4px solid #0ea5e9;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <h3 style="font-size:17px;margin-bottom:8px;color:#1e1e2f;">Financial Reconciliation</h3>
                        <p style="color:#666;font-size:13px;margin-bottom:16px;">Compare account balances with recorded transactions and check general ledger debit and credit integrity.</p>
                    </div>
                    <a href="<?= base_url('reconciliation') ?>" style="display:inline-block;padding:8px 14px;background:#0ea5e9;color:white;text-decoration:none;border-radius:6px;font-size:13px;font-weight:600;width:fit-content;">Open Reconciliation Hub &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
