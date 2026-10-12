<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Account Statement') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4cc9f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }

        .navbar { background: #1e1e2f; padding: 16px 36px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; }

        .container { max-width: 1200px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 20px; }

        .account-hero {
            background: linear-gradient(135deg, #1e1e2f, #2a2a40);
            color: white;
            border-radius: var(--radius-lg);
            padding: 32px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .hero-title p { font-size: 13px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .hero-title h1 { font-size: 30px; font-weight: 800; color: #ffffff; letter-spacing: 1px; margin-top: 4px; }
        
        .hero-balance { text-align: right; }
        .hero-balance p { font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 600; }
        .hero-balance h2 { font-size: 32px; font-weight: 800; color: #4ade80; margin-top: 4px; }

        .hero-actions { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
        .btn-action-hero { padding: 9px 18px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-deposit-hero { background: #10b981; color: white; }
        .btn-withdraw-hero { background: #ef4444; color: white; }
        .btn-customer-hero { background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.2); }

        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        @media (max-width: 800px) { .grid-3 { grid-template-columns: 1fr; } }
        .meta-card { background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; box-shadow: var(--shadow-sm); }
        .meta-card p { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
        .meta-card h3 { font-size: 16px; font-weight: 700; margin-top: 4px; color: var(--text-main); }

        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 16px; font-weight: 700; color: #1e1e2f; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 12px 18px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        td { padding: 14px 18px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .badge-deposit { background: #ecfdf5; color: #065f46; font-weight: 700; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
        .badge-withdrawal { background: #fef2f2; color: #991b1b; font-weight: 700; padding: 4px 8px; border-radius: 4px; font-size: 11px; }

        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="<?= base_url('dashboard') ?>" class="nav-brand">
            <div class="brand-logo">BM</div>
            <div><h2 style="font-size: 16px; font-weight: 700;">Bank Management System</h2></div>
        </a>
        <nav class="nav-links">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">Dashboard</a>
            <a href="<?= base_url('customers') ?>" class="nav-link">Customers</a>
            <a href="<?= base_url('accounts') ?>" class="nav-link">Accounts</a>
            <a href="<?= base_url('deposits') ?>" class="nav-link">Deposits</a>
            <a href="<?= base_url('withdrawals') ?>" class="nav-link">Withdrawals</a>
            <a href="<?= base_url('logout') ?>" class="nav-link" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <main class="container">
        <a href="<?= base_url('accounts') ?>" class="back-link">&larr; Back to Accounts List</a>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="account-hero">
            <div>
                <div class="hero-title">
                    <p><?= esc($account['account_type_name']) ?> &bull; Branch: <?= esc($account['branch_name'] ?? 'Main') ?></p>
                    <h1><?= esc($account['account_no']) ?></h1>
                </div>
                <div class="hero-actions">
                    <a href="<?= base_url('deposits/create?account_no=' . $account['account_no']) ?>" class="btn-action-hero btn-deposit-hero">+ Cash Deposit</a>
                    <a href="<?= base_url('withdrawals/create?account_no=' . $account['account_no']) ?>" class="btn-action-hero btn-withdraw-hero">- Cash Withdrawal</a>
                    <a href="<?= base_url('customers/view/' . $account['customer_id']) ?>" class="btn-action-hero btn-customer-hero">Customer Profile</a>
                </div>
            </div>
            <div class="hero-balance">
                <p>Available Balance</p>
                <h2>Rs. <?= number_format((float)$account['balance'], 2) ?></h2>
                <div style="margin-top: 6px; font-size: 12px; color: #cbd5e1;">
                    Status: <span style="text-transform: uppercase; font-weight: 700; color: <?= $account['status'] === 'active' ? '#4ade80' : '#f87171' ?>;">● <?= esc($account['status']) ?></span>
                </div>
            </div>
        </div>

        <div class="grid-3">
            <div class="meta-card">
                <p>Account Holder</p>
                <h3><?= esc($account['customer_name']) ?></h3>
                <small style="color: var(--text-muted);">NIC: <?= esc($account['customer_nic'] ?? '—') ?> &bull; Phone: <?= esc($account['customer_phone'] ?? '—') ?></small>
            </div>
            <div class="meta-card">
                <p>Interest &amp; Minimum Balance</p>
                <h3><?= number_format((float)$account['interest_rate'], 2) ?>% p.a.</h3>
                <small style="color: var(--text-muted);">Min Balance: Rs. <?= number_format((float)$account['min_balance'], 2) ?></small>
            </div>
            <div class="meta-card">
                <p>Lifecycle Status Control</p>
                <form action="<?= base_url('accounts/toggle-status/' . $account['id']) ?>" method="post" style="display: flex; gap: 8px; margin-top: 6px;">
                    <?= csrf_field() ?>
                    <select name="status" style="padding: 6px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 12px;">
                        <option value="active" <?= $account['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $account['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="closed" <?= $account['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                    <button type="submit" style="padding: 6px 12px; background: #f1f5f9; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; cursor: pointer;">Update</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Transaction History &amp; Ledger Statement</h2>
                <span style="font-size: 12px; color: var(--text-muted);">Showing recent deposits and withdrawals</span>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Type</th>
                            <th>Reference / ID</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Previous Balance</th>
                            <th>New Balance</th>
                            <th>Teller</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($history)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    No deposit or withdrawal transactions recorded on this account yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($history as $item): ?>
                                <tr>
                                    <td><?= date('M d, Y H:i', strtotime($item['date'])) ?></td>
                                    <td>
                                        <?php if($item['type'] === 'DEPOSIT'): ?>
                                            <span class="badge-deposit">+ DEPOSIT</span>
                                        <?php else: ?>
                                            <span class="badge-withdrawal">- WITHDRAWAL</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($item['type'] === 'DEPOSIT'): ?>
                                            <a href="<?= base_url('deposits/receipt/' . $item['id']) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                                <?= esc($item['ref']) ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('withdrawals/receipt/' . $item['id']) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                                <?= esc($item['ref']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($item['method']) ?></td>
                                    <td style="font-weight: 700; color: <?= $item['type'] === 'DEPOSIT' ? 'var(--success)' : 'var(--danger)' ?>;">
                                        <?= $item['type'] === 'DEPOSIT' ? '+' : '-' ?> Rs. <?= number_format($item['amount'], 2) ?>
                                    </td>
                                    <td>Rs. <?= number_format($item['previous_balance'], 2) ?></td>
                                    <td style="font-weight: 700; color: #1e1e2f;">Rs. <?= number_format($item['new_balance'], 2) ?></td>
                                    <td><?= esc($item['teller']) ?></td>
                                    <td><?= esc($item['remarks'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
