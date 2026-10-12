<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Customer Profile') ?></title>
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
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.08); }

        .container { max-width: 1200px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 20px; }

        .profile-header {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: 28px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: var(--shadow-sm);
        }
        .profile-meta h1 { font-size: 24px; font-weight: 800; color: #1e1e2f; }
        .profile-badges { display: flex; gap: 10px; margin-top: 8px; align-items: center; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-verified { background: #ecfdf5; color: var(--success); border: 1px solid #a7f3d0; }
        .badge-pending { background: #fffbeb; color: var(--warning); border: 1px solid #fde68a; }
        .badge-rejected { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }

        .grid-2 { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }

        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px; }
        .card-title { font-size: 16px; font-weight: 700; margin-bottom: 18px; color: #1e1e2f; display: flex; justify-content: space-between; align-items: center; }

        .info-list { display: flex; flex-direction: column; gap: 14px; }
        .info-item { display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 8px; font-size: 13px; }
        .info-label { color: var(--text-muted); font-weight: 500; }
        .info-value { font-weight: 600; color: #1e1e2f; text-align: right; }

        .stat-banner { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .stat-box { background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; }
        .stat-box p { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
        .stat-box h2 { font-size: 24px; font-weight: 800; color: var(--primary); margin-top: 4px; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        td { padding: 14px 16px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-light { background: #f1f5f9; color: var(--text-main); padding: 9px 16px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid var(--border); }
        .btn-sm { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; }

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
        <a href="<?= base_url('customers') ?>" class="back-link">&larr; Back to Customers Directory</a>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-meta">
                <div style="font-size: 12px; color: var(--primary); font-weight: 700; text-transform: uppercase;">Customer ID: <?= esc($customer['customer_id']) ?></div>
                <h1><?= esc($customer['full_name']) ?></h1>
                <div class="profile-badges">
                    <?php if($customer['kyc_status'] === 'Verified'): ?>
                        <span class="badge badge-verified">✓ KYC Verified</span>
                    <?php elseif($customer['kyc_status'] === 'Rejected'): ?>
                        <span class="badge badge-rejected">✕ KYC Rejected</span>
                    <?php else: ?>
                        <span class="badge badge-pending">⏱ KYC Verification Pending</span>
                    <?php endif; ?>
                    <span style="font-size: 12px; color: var(--text-muted);">&bull; Registered: <?= date('M d, Y', strtotime($customer['created_at'])) ?></span>
                    <span style="font-size: 12px; color: var(--text-muted);">&bull; Branch: <strong><?= esc($customer['branch_name'] ?? 'Main') ?></strong></span>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="<?= base_url('accounts/open?customer_id=' . $customer['id']) ?>" class="btn-primary">+ Open Bank Account</a>
                <a href="<?= base_url('customers/edit/' . $customer['id']) ?>" class="btn-light">Edit Profile</a>
            </div>
        </div>

        <div class="grid-2">
            <!-- KYC & Customer Details Column -->
            <div>
                <div class="card">
                    <div class="card-title">KYC & Identity Details</div>
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">NIC / Passport</span>
                            <span class="info-value"><?= esc($customer['nic'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value"><?= !empty($customer['dob']) ? date('M d, Y', strtotime($customer['dob'])) : '—' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Occupation</span>
                            <span class="info-value"><?= esc($customer['occupation'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Telephone</span>
                            <span class="info-value"><?= esc($customer['phone'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?= esc($customer['email'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address</span>
                            <span class="info-value" style="max-width: 200px;"><?= esc($customer['address'] ?? '—') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Branch</span>
                            <span class="info-value"><?= esc($customer['branch_name'] ?? 'Not assigned') ?></span>
                        </div>
                    </div>

                    <!-- KYC Review Form -->
                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px;">Review KYC Status</div>
                        <form action="<?= base_url('customers/kyc/' . $customer['id']) ?>" method="post" style="display: flex; gap: 8px;">
                            <?= csrf_field() ?>
                            <select name="kyc_status" style="flex: 1; padding: 7px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 12px;">
                                <option value="Verified" <?= $customer['kyc_status'] === 'Verified' ? 'selected' : '' ?>>Verified</option>
                                <option value="Pending" <?= $customer['kyc_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Rejected" <?= $customer['kyc_status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <button type="submit" class="btn-primary" style="padding: 7px 14px; font-size: 12px;">Update</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Customer Accounts Column -->
            <div>
                <div class="stat-banner">
                    <div class="stat-box">
                        <p>Total Deposit Balance</p>
                        <h2>Rs. <?= number_format($summary['total_balance'], 2) ?></h2>
                    </div>
                    <div class="stat-box">
                        <p>Active Accounts</p>
                        <h2><?= number_format($summary['account_count']) ?></h2>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">
                        <span>Customer Bank Accounts</span>
                        <a href="<?= base_url('accounts/open?customer_id=' . $customer['id']) ?>" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;">+ New Account</a>
                    </div>

                    <?php if(empty($accounts)): ?>
                        <div style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 14px;">
                            This customer does not have any bank accounts yet.
                            <div style="margin-top: 12px;">
                                <a href="<?= base_url('accounts/open?customer_id=' . $customer['id']) ?>" class="btn-primary">+ Open First Account</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Account No</th>
                                        <th>Account Type</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($accounts as $acc): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('accounts/view/' . $acc['id']) ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                                                    <?= esc($acc['account_no']) ?>
                                                </a>
                                            </td>
                                            <td><?= esc($acc['account_type_name']) ?></td>
                                            <td style="font-weight: 700; color: #1e1e2f;">
                                                Rs. <?= number_format((float)$acc['balance'], 2) ?>
                                            </td>
                                            <td>
                                                <?php if($acc['status'] === 'active'): ?>
                                                    <span style="color: var(--success); font-weight: 600; font-size: 12px;">● Active</span>
                                                <?php else: ?>
                                                    <span style="color: var(--danger); font-weight: 600; font-size: 12px;">● <?= ucfirst(esc($acc['status'])) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 6px;">
                                                    <a href="<?= base_url('deposits/create?account_no=' . $acc['account_no']) ?>" class="btn-sm" style="background: #ecfdf5; color: #065f46;">Deposit</a>
                                                    <a href="<?= base_url('withdrawals/create?account_no=' . $acc['account_no']) ?>" class="btn-sm" style="background: #fef2f2; color: #991b1b;">Withdraw</a>
                                                    <a href="<?= base_url('accounts/view/' . $acc['id']) ?>" class="btn-sm" style="background: #f1f5f9; color: var(--text-main);">Details</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
