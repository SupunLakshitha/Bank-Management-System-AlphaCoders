<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Withdrawals Journal') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
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

        .navbar { background: #1e1e2f; padding: 16px 36px; display: flex; justify-content: space-between; align-items: center; color: white; box-shadow: 0 4px 20px rgba(0,0,0,0.12); position: sticky; top: 0; z-index: 100; }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), #4cc9f0); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.08); }

        .container { max-width: 1240px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .page-header h1 { font-size: 26px; font-weight: 800; color: #1e1e2f; }
        .page-header p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }

        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; text-decoration: none; padding: 10px 20px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); border: none; cursor: pointer; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
        .stat-card p { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .stat-card h3 { font-size: 24px; font-weight: 800; margin-top: 6px; color: var(--text-main); }

        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid var(--border); }

        .search-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .form-control, .form-select { padding: 9px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; outline: none; }
        .btn-filter { padding: 9px 16px; background: #f1f5f9; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 14px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        td { padding: 14px 20px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover td { background: #fcfcfd; }
        tr:last-child td { border-bottom: none; }

        .badge-method { background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .btn-sm { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; background: #fee2e2; color: #991b1b; }

        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
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
            <a href="<?= base_url('withdrawals') ?>" class="nav-link active">Withdrawals</a>
            <a href="<?= base_url('logout') ?>" class="nav-link" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <main class="container">
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Customer Withdrawals Journal</h1>
                <p>Audited ledger of funds debited from customer bank accounts with balance verification.</p>
            </div>
            <a href="<?= base_url('withdrawals/create') ?>" class="btn-danger">- Process New Withdrawal</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 4px solid var(--danger);">
                <p>Total Withdrawal Records</p>
                <h3><?= number_format(count($withdrawals)) ?></h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid #dc2626;">
                <p>Total Funds Withdrawn</p>
                <h3>Rs. <?= number_format($totalWithdrawn, 2) ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="<?= base_url('withdrawals') ?>" method="get" class="search-bar">
                    <input type="text" name="q" class="form-control" placeholder="Search Withdrawal ID, Account No, Ref..." value="<?= esc($filters['q'] ?? '') ?>" style="min-width: 260px;">
                    <select name="method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="Cash" <?= ($filters['method'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="Cheque" <?= ($filters['method'] ?? '') === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                        <option value="ATM" <?= ($filters['method'] ?? '') === 'ATM' ? 'selected' : '' ?>>ATM</option>
                        <option value="Online Transfer" <?= ($filters['method'] ?? '') === 'Online Transfer' ? 'selected' : '' ?>>Online Transfer</option>
                    </select>
                    <input type="date" name="from" class="form-control" value="<?= esc($filters['from'] ?? '') ?>" title="From Date">
                    <input type="date" name="to" class="form-control" value="<?= esc($filters['to'] ?? '') ?>" title="To Date">
                    <button type="submit" class="btn-filter">Filter</button>
                    <?php if(!empty($filters['q']) || !empty($filters['method']) || !empty($filters['from']) || !empty($filters['to'])): ?>
                        <a href="<?= base_url('withdrawals') ?>" class="btn-filter" style="color: var(--text-muted);">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Withdrawal ID</th>
                            <th>Date / Time</th>
                            <th>Account No</th>
                            <th>Customer Name</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Prev &rarr; New Balance</th>
                            <th>Teller</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($withdrawals)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    No withdrawal transactions found. Click <strong>- Process New Withdrawal</strong> to initiate an over-the-counter cash payout.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($withdrawals as $w): ?>
                                <tr>
                                    <td><strong style="color: var(--danger);"><?= esc($w['withdrawal_id']) ?></strong></td>
                                    <td><?= date('M d, Y H:i', strtotime($w['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('accounts/view/' . $w['account_id']) ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                            <?= esc($w['account_no']) ?>
                                        </a>
                                    </td>
                                    <td><?= esc($w['customer_name'] ?? '—') ?></td>
                                    <td><span class="badge-method"><?= esc($w['withdrawal_method']) ?></span></td>
                                    <td style="font-weight: 700; color: var(--danger);">
                                        - Rs. <?= number_format((float)$w['amount'], 2) ?>
                                    </td>
                                    <td>
                                        <span style="color: var(--text-muted);">Rs. <?= number_format((float)$w['previous_balance'], 2) ?></span>
                                        &rarr;
                                        <strong>Rs. <?= number_format((float)$w['new_balance'], 2) ?></strong>
                                    </td>
                                    <td><?= esc($w['teller_username'] ?? 'System') ?></td>
                                    <td>
                                        <a href="<?= base_url('withdrawals/receipt/' . $w['id']) ?>" class="btn-sm">Receipt</a>
                                    </td>
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
