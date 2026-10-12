<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Bank Accounts') ?></title>
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

        .navbar {
            background: #1e1e2f;
            padding: 16px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.08); }
        .btn-logout { padding: 7px 14px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; color: #fca5a5; text-decoration: none; font-size: 13px; font-weight: 600; }
        .btn-logout:hover { background: var(--danger); color: white; }

        .container { max-width: 1240px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .page-header h1 { font-size: 26px; font-weight: 800; color: #1e1e2f; }
        .page-header p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            border: none;
            cursor: pointer;
        }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        .stat-card p { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .stat-card h3 { font-size: 24px; font-weight: 800; margin-top: 6px; color: var(--text-main); }

        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 30px; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid var(--border); }

        .search-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .form-control, .form-select { padding: 9px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; outline: none; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15); }
        .btn-filter { padding: 9px 16px; background: #f1f5f9; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 14px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        td { padding: 14px 20px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover td { background: #fcfcfd; }
        tr:last-child td { border-bottom: none; }

        .badge-type { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-active { background: #ecfdf5; color: var(--success); }
        .badge-suspended { background: #fffbeb; color: var(--warning); }
        .badge-closed { background: #fef2f2; color: var(--danger); }

        .actions { display: flex; gap: 6px; }
        .btn-sm { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; }
        .btn-deposit { background: #ecfdf5; color: #065f46; }
        .btn-withdraw { background: #fef2f2; color: #991b1b; }
        .btn-view { background: #f1f5f9; color: var(--text-main); }

        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
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
            <a href="<?= base_url('accounts') ?>" class="nav-link active">Accounts</a>
            <a href="<?= base_url('deposits') ?>" class="nav-link">Deposits</a>
            <a href="<?= base_url('withdrawals') ?>" class="nav-link">Withdrawals</a>
            <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
        </nav>
    </header>

    <main class="container">
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Bank Accounts Management</h1>
                <p>Monitor customer balances, open savings & current facilities, and control account lifecycles.</p>
            </div>
            <a href="<?= base_url('accounts/open') ?>" class="btn-primary">+ Open Bank Account</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 4px solid var(--primary);">
                <p>Total Bank Accounts</p>
                <h3><?= number_format(count($accounts)) ?></h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--success);">
                <p>Total Deposits On Record</p>
                <h3>Rs. <?= number_format($totalDepositsSum, 2) ?></h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid #0ea5e9;">
                <p>Account Types Offered</p>
                <h3><?= number_format(count($types)) ?> Types</h3>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="<?= base_url('accounts') ?>" method="get" class="search-bar">
                    <input type="text" name="q" class="form-control" placeholder="Search account number, customer name, NIC..." value="<?= esc($filters['q'] ?? '') ?>" style="min-width: 260px;">
                    <select name="type_id" class="form-select">
                        <option value="">All Account Types</option>
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= ($filters['type_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        <?php foreach($branches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= ($filters['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-filter">Filter</button>
                    <?php if(!empty($filters['q']) || !empty($filters['type_id']) || !empty($filters['status']) || !empty($filters['branch_id'])): ?>
                        <a href="<?= base_url('accounts') ?>" class="btn-filter" style="color: var(--text-muted);">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Account No</th>
                            <th>Customer Name</th>
                            <th>Account Type</th>
                            <th>Current Balance</th>
                            <th>Interest Rate</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($accounts)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    No bank accounts found matching criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($accounts as $acc): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('accounts/view/' . $acc['id']) ?>" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                            <?= esc($acc['account_no']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e1e2f;"><?= esc($acc['customer_name'] ?? '—') ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= esc($acc['cust_code'] ?? '') ?> &bull; NIC: <?= esc($acc['customer_nic'] ?? '—') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-type"><?= esc($acc['account_type_name']) ?></span>
                                    </td>
                                    <td style="font-weight: 700; font-size: 14px; color: #1e1e2f;">
                                        Rs. <?= number_format((float)$acc['balance'], 2) ?>
                                    </td>
                                    <td><?= number_format((float)$acc['interest_rate'], 2) ?>%</td>
                                    <td><?= esc($acc['branch_name'] ?? 'Main') ?></td>
                                    <td>
                                        <span class="badge-status badge-<?= esc($acc['status']) ?>">
                                            ● <?= ucfirst(esc($acc['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="<?= base_url('deposits/create?account_no=' . $acc['account_no']) ?>" class="btn-sm btn-deposit" title="Deposit Money">Deposit</a>
                                            <a href="<?= base_url('withdrawals/create?account_no=' . $acc['account_no']) ?>" class="btn-sm btn-withdraw" title="Withdraw Money">Withdraw</a>
                                            <a href="<?= base_url('accounts/view/' . $acc['id']) ?>" class="btn-sm btn-view">Statement</a>
                                        </div>
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
