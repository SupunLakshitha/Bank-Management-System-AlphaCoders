<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($branch['name']) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f4f6f9; color: #333; }
        .navbar { background: #2a2a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar h2 { font-weight: 600; font-size: 20px; color: #4facfe; }
        .nav-links { display: flex; gap: 12px; align-items: center; }
        .nav-links a { color: #d6e4ff; text-decoration: none; font-size: 14px; }
        .btn-logout { padding: 8px 15px; background: rgba(255, 77, 79, 0.1); border: 1px solid #ff4d4f; border-radius: 5px; color: #ff4d4f; text-decoration: none; }
        .container { max-width: 1100px; margin: 32px auto; padding: 0 20px; }
        .card { background: white; padding: 24px; border-radius: 14px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        h1 { font-size: 24px; color: #1e1e2f; margin-bottom: 6px; }
        h3 { margin-bottom: 12px; color: #1e1e2f; }
        .meta { color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eee; }
        th { color: #666; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        .empty { color: #888; font-size: 14px; }
        .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: rgba(255, 77, 79, 0.1); border: 1px solid #ff4d4f; color: #cf1322; }
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #047857; }
        .assign { display: flex; gap: 10px; align-items: center; }
        select, button { padding: 10px 12px; border-radius: 8px; }
        select { border: 1px solid #d9d9e3; min-width: 280px; }
        button { background: #4361ee; color: white; border: none; font-weight: 600; cursor: pointer; }
        .code { display: inline-block; background: #e6f7ff; color: #096dd9; border-radius: 4px; padding: 2px 8px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>BMS Branches</h2>
        <div class="nav-links">
            <a href="<?= base_url('branches') ?>">All branches</a>
            <a href="<?= base_url('dashboard') ?>">Dashboard</a>
            <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <div class="card">
            <span class="code"><?= esc($branch['code']) ?></span>
            <h1 style="margin-top: 8px;"><?= esc($branch['name']) ?></h1>
            <p class="meta"><?= esc($branch['location']) ?></p>
            <p class="meta">Customers, accounts, employees, transactions, and loans listed here are recorded against this branch only.</p>
        </div>

        <div class="card">
            <h3>Employees</h3>
            <?php if ($employees): ?>
                <table>
                    <thead><tr><th>Username</th><th>Role</th></tr></thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= esc($employee['username']) ?></td>
                                <td><?= esc($employee['role_name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No employees are assigned to this branch.</p>
            <?php endif; ?>

            <?php if ($role === 'SUPER ADMIN' && $unassignedEmployees): ?>
                <form class="assign" style="margin-top: 16px;" action="<?= base_url('branches/' . $branch['id'] . '/assign') ?>" method="post">
                    <select name="user_id" required>
                        <option value="">Assign an employee to this branch</option>
                        <?php foreach ($unassignedEmployees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>">
                                <?= esc($employee['username']) ?> — <?= esc($employee['role_name']) ?> (<?= esc($employee['branch_name'] ?? 'Unassigned') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Assign</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Customers</h3>
            <?php if ($customers): ?>
                <table>
                    <thead><tr><th>Customer ID</th><th>Name</th><th>Username</th><th>Phone</th><th>KYC</th></tr></thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= esc($customer['customer_id']) ?></td>
                                <td><?= esc($customer['full_name']) ?></td>
                                <td><?= esc($customer['username'] ?? '') ?></td>
                                <td><?= esc($customer['phone']) ?></td>
                                <td><?= esc($customer['kyc_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No customers are registered at this branch.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Accounts</h3>
            <?php if ($accounts): ?>
                <table>
                    <thead><tr><th>Account</th><th>Customer</th><th>Type</th><th>Balance</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><?= esc($account['account_no']) ?></td>
                                <td><?= esc($account['customer_id']) ?> — <?= esc($account['full_name']) ?></td>
                                <td><?= esc($account['account_type']) ?></td>
                                <td>Rs. <?= number_format((float) $account['balance'], 2) ?></td>
                                <td><?= esc($account['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No accounts are held at this branch.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Transactions</h3>
            <?php if ($transactions): ?>
                <table>
                    <thead><tr><th>Reference</th><th>Account</th><th>Customer</th><th>Type</th><th>Amount</th><th>When</th></tr></thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= esc($transaction['tx_id']) ?></td>
                                <td><?= esc($transaction['account_no']) ?></td>
                                <td><?= esc($transaction['full_name']) ?></td>
                                <td><?= esc($transaction['type']) ?></td>
                                <td>Rs. <?= number_format((float) $transaction['amount'], 2) ?></td>
                                <td><?= esc($transaction['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No transactions have been posted at this branch.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Loans</h3>
            <?php if ($loans): ?>
                <table>
                    <thead><tr><th>Loan account</th><th>Customer</th><th>Principal</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?= esc($loan['loan_account_no']) ?></td>
                                <td><?= esc($loan['customer_id']) ?> — <?= esc($loan['full_name']) ?></td>
                                <td>Rs. <?= number_format((float) $loan['principal_amount'], 2) ?></td>
                                <td><?= esc($loan['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No loans are booked at this branch.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
