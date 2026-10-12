<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Customer Management') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4cc9f0;
            --secondary: #7209b7;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.08);
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
        .brand-logo {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 16px;
        }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.08); }
        .btn-logout {
            padding: 7px 14px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            color: #fca5a5;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
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
        .btn-primary:hover { transform: translateY(-1px); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        .stat-card p { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .stat-card h3 { font-size: 26px; font-weight: 800; margin-top: 6px; color: var(--text-main); }

        .card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .card-header { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }

        .search-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .form-control, .form-select {
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            outline: none;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15); }
        .btn-filter { padding: 9px 16px; background: #f1f5f9; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; cursor: pointer; color: var(--text-main); text-decoration: none; }
        .btn-filter:hover { background: #e2e8f0; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 14px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        td { padding: 14px 20px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover td { background: #fcfcfd; }
        tr:last-child td { border-bottom: none; }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-verified { background: #ecfdf5; color: var(--success); border: 1px solid #a7f3d0; }
        .badge-pending { background: #fffbeb; color: var(--warning); border: 1px solid #fde68a; }
        .badge-rejected { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }

        .actions { display: flex; gap: 6px; align-items: center; }
        .btn-sm { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
        .btn-view { background: #e0e7ff; color: #4338ca; }
        .btn-view:hover { background: #c7d2fe; }
        .btn-edit { background: #f1f5f9; color: var(--text-main); }
        .btn-edit:hover { background: #e2e8f0; }

        .alert { padding: 12px 18px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 20px; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="<?= base_url('dashboard') ?>" class="nav-brand">
            <div class="brand-logo">BM</div>
            <div>
                <h2 style="font-size: 16px; font-weight: 700;">Bank Management System</h2>
            </div>
        </a>
        <nav class="nav-links">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">Dashboard</a>
            <a href="<?= base_url('customers') ?>" class="nav-link active">Customers</a>
            <a href="<?= base_url('accounts') ?>" class="nav-link">Accounts</a>
            <a href="<?= base_url('deposits') ?>" class="nav-link">Deposits</a>
            <a href="<?= base_url('withdrawals') ?>" class="nav-link">Withdrawals</a>
            <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
        </nav>
    </header>

    <main class="container">
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>Customer Profiles & KYC</h1>
                <p>Manage customer directory, contact details, identity verification, and accounts.</p>
            </div>
            <a href="<?= base_url('customers/create') ?>" class="btn-primary">+ Onboard New Customer</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 4px solid var(--primary);">
                <p>Total Customers</p>
                <h3><?= number_format($stats['total']) ?></h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--success);">
                <p>KYC Verified</p>
                <h3><?= number_format($stats['verified']) ?></h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--warning);">
                <p>KYC Pending Review</p>
                <h3><?= number_format($stats['pending']) ?></h3>
            </div>
            <div class="stat-card" style="border-left: 4px solid var(--danger);">
                <p>KYC Rejected</p>
                <h3><?= number_format($stats['rejected']) ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form action="<?= base_url('customers') ?>" method="get" class="search-bar">
                    <input type="text" name="q" class="form-control" placeholder="Search by name, ID, NIC, phone..." value="<?= esc($filters['q'] ?? '') ?>" style="min-width: 260px;">
                    <select name="kyc_status" class="form-select">
                        <option value="">All KYC Statuses</option>
                        <option value="Verified" <?= ($filters['kyc_status'] ?? '') === 'Verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="Pending" <?= ($filters['kyc_status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Rejected" <?= ($filters['kyc_status'] ?? '') === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        <?php foreach($branches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= ($filters['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-filter">Search</button>
                    <?php if(!empty($filters['q']) || !empty($filters['kyc_status']) || !empty($filters['branch_id'])): ?>
                        <a href="<?= base_url('customers') ?>" class="btn-filter" style="color: var(--text-muted);">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Full Name</th>
                            <th>NIC / Passport</th>
                            <th>Contact Phone & Email</th>
                            <th>Branch</th>
                            <th>KYC Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($customers)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    No customer records found. Click <strong>+ Onboard New Customer</strong> to register a customer.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($customers as $c): ?>
                                <tr>
                                    <td><strong style="color: var(--primary);"><?= esc($c['customer_id']) ?></strong></td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e1e2f;"><?= esc($c['full_name']) ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= esc($c['occupation'] ?? 'Customer') ?></div>
                                    </td>
                                    <td><?= esc($c['nic'] ?? '—') ?></td>
                                    <td>
                                        <div><?= esc($c['phone'] ?? '—') ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= esc($c['email'] ?? '') ?></div>
                                    </td>
                                    <td><?= esc($c['branch_name'] ?? 'Main') ?></td>
                                    <td>
                                        <?php if($c['kyc_status'] === 'Verified'): ?>
                                            <span class="badge badge-verified">✓ Verified</span>
                                        <?php elseif($c['kyc_status'] === 'Rejected'): ?>
                                            <span class="badge badge-rejected">✕ Rejected</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">⏱ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="<?= base_url('customers/view/' . $c['id']) ?>" class="btn-sm btn-view">View Profile</a>
                                            <a href="<?= base_url('customers/edit/' . $c['id']) ?>" class="btn-sm btn-edit">Edit</a>
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
