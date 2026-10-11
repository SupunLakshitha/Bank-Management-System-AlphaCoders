<?php
$active   = $active ?? '';
$username = $username ?? 'User';
$role     = $role ?? '';
$title    = $title ?? 'Bank Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
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
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        body { background: var(--bg); color: var(--text-main); min-height: 100vh; }
        .navbar {
            background: #1e1e2f;
            padding: 16px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .brand-logo {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex; align-items: center; justify-content: center; font-weight: 800;
        }
        .nav-brand h2 { font-size: 18px; font-weight: 700; }
        .nav-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .user-pill {
            background: rgba(255,255,255,0.08);
            padding: 6px 14px; border-radius: 30px; font-size: 13px; color: #e2e8f0;
            display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.1);
        }
        .user-pill .badge { background: var(--primary); color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .btn-nav { padding: 8px 14px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; text-decoration: none; }
        .btn-dash { background: rgba(255,255,255,0.1); color: white; }
        .btn-dash.active, .btn-dash:hover { background: rgba(255,255,255,0.2); }
        .btn-logout { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .btn-logout:hover { background: var(--danger); color: white; }
        .container { max-width: 1200px; margin: 32px auto; padding: 0 24px 48px; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
        .page-header h1 { font-size: 26px; font-weight: 800; letter-spacing: -0.4px; }
        .page-header p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: white; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px 20px; }
        .stat-card p { color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
        .stat-card h3 { font-size: 22px; margin-top: 6px; }
        .card { background: white; border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
        .card-header { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .card-header h2 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 12px 18px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); text-align: left; border-bottom: 1px solid var(--border); }
        td { padding: 14px 18px; border-bottom: 1px solid var(--border); font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .muted { color: var(--text-muted); font-size: 12px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-green { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-blue { background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-purple { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
        .badge-amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-slate { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
        .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .btn-primary, button.btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; border: none; padding: 12px 18px; border-radius: var(--radius-sm);
            font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-ghost { color: var(--primary); font-weight: 700; text-decoration: none; font-size: 13px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field.full { grid-column: 1 / -1; }
        .field label { font-size: 13px; font-weight: 700; }
        .field input, .field select, .field textarea {
            width: 100%; padding: 12px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px; background: white;
        }
        .split { display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 20px; margin-bottom: 20px; }
        .pad { padding: 20px 22px; }
        .empty { padding: 48px 20px; text-align: center; color: var(--text-muted); }
        .balance-ok { color: var(--success); font-weight: 700; }
        .balance-bad { color: var(--danger); font-weight: 700; }
        @media (max-width: 860px) {
            .split, .form-grid { grid-template-columns: 1fr; }
            .navbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a href="<?= base_url('dashboard') ?>" class="nav-brand">
        <div class="brand-logo">B</div>
        <h2>Bank Management</h2>
    </a>
    <div class="nav-actions">
        <div class="user-pill">
            <span><?= esc($username) ?></span>
            <span class="badge"><?= esc($role) ?></span>
        </div>
        <a href="<?= base_url('dashboard') ?>" class="btn-nav btn-dash <?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= base_url('ledger') ?>" class="btn-nav btn-dash <?= $active === 'ledger' ? 'active' : '' ?>">Ledger</a>
        <a href="<?= base_url('ledger/chart') ?>" class="btn-nav btn-dash <?= $active === 'chart' ? 'active' : '' ?>">Chart</a>
        <?php if (in_array($role, ['SUPER ADMIN', 'Branch Manager', 'Account Officer', 'Teller'], true)): ?>
            <a href="<?= base_url('transfers') ?>" class="btn-nav btn-dash <?= $active === 'transfers' ? 'active' : '' ?>">Transfers</a>
        <?php endif; ?>
        <a href="<?= base_url('logout') ?>" class="btn-nav btn-logout">Logout</a>
    </div>
</nav>
<div class="container">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
