<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Dashboard</title>
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

        <?php if ($role !== 'Customer'): ?>
            <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-left: 4px solid #4facfe; margin-bottom: 20px;">
                <h3 style="font-size: 17px; margin-bottom: 8px; color: #1e1e2f;">Branch Management</h3>
                <p style="color: #666; font-size: 13px; margin-bottom: 16px;">Customers, accounts, employees, transactions, and loans are kept with their branch.</p>
                <a href="<?= base_url('branches') ?>" style="display: inline-block; padding: 8px 16px; background: #4facfe; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600;">Open branches &rarr;</a>
            </div>
        <?php endif; ?>

        <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager', 'Loan Officer'])): ?>
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
    </div>

</body>
</html>
