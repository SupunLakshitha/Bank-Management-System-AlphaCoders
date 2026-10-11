<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Fixed Deposits') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#4361ee; --primary-dark:#3a0ca3; --primary-light:#4cc9f0; --success:#10b981; --warning:#f59e0b; --danger:#ef4444; --bg:#f8fafc; --card-bg:#fff; --text-main:#0f172a; --text-muted:#64748b; --border:#e2e8f0; --shadow-sm:0 1px 3px rgba(0,0,0,.05); --shadow-lg:0 10px 25px rgba(0,0,0,.08); --radius-sm:8px; --radius-md:12px; --radius-lg:16px; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans','Inter',sans-serif; }
        body { background:var(--bg); color:var(--text-main); min-height:100vh; }
        .navbar { background:#1e1e2f; padding:16px 36px; display:flex; justify-content:space-between; align-items:center; color:#fff; box-shadow:0 4px 20px rgba(0,0,0,.12); position:sticky; top:0; z-index:100; flex-wrap:wrap; gap:12px; }
        .nav-brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:#fff; }
        .brand-logo { width:36px; height:36px; background:linear-gradient(135deg,var(--primary),var(--primary-light)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; }
        .nav-brand h2 { font-size:18px; font-weight:700; }
        .nav-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .user-pill { background:rgba(255,255,255,.08); padding:6px 14px; border-radius:30px; font-size:13px; color:#e2e8f0; display:flex; gap:8px; border:1px solid rgba(255,255,255,.1); }
        .user-pill .badge { background:var(--primary); color:#fff; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; }
        .btn-nav { padding:8px 14px; border-radius:var(--radius-sm); font-size:13px; font-weight:600; text-decoration:none; background:rgba(255,255,255,.1); color:#fff; }
        .btn-nav:hover { background:rgba(255,255,255,.2); }
        .btn-logout { background:rgba(239,68,68,.15); border:1px solid rgba(239,68,68,.3); color:#fca5a5; }
        .btn-logout:hover { background:var(--danger); color:#fff; }
        .container { max-width:1180px; width:100%; margin:32px auto; padding:0 24px; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:16px; }
        .page-header h1 { font-size:26px; font-weight:800; letter-spacing:-.5px; }
        .page-header p { color:var(--text-muted); font-size:14px; margin-top:4px; }
        .btn-primary { background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:#fff; border:none; padding:12px 22px; border-radius:var(--radius-sm); font-size:14px; font-weight:600; cursor:pointer; box-shadow:0 4px 14px rgba(67,97,238,.35); text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
        .btn-primary:hover { transform:translateY(-2px); }
        .btn-light { background:#f1f5f9; color:var(--text-main); border:none; padding:12px 20px; border-radius:var(--radius-sm); font-weight:600; cursor:pointer; text-decoration:none; font-size:14px; }
        .alert { padding:14px 18px; border-radius:var(--radius-sm); font-size:14px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; transition:opacity .4s; }
        .alert.fade-out { opacity:0; }
        .alert-success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .alert-danger { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .alert-warning { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
        .alert-close { background:transparent; border:none; cursor:pointer; font-size:18px; color:inherit; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:28px; }
        .stat-card { background:var(--card-bg); border-radius:var(--radius-md); padding:22px; border:1px solid var(--border); box-shadow:var(--shadow-sm); }
        .stat-card h3 { font-size:24px; font-weight:800; }
        .stat-card p { font-size:13px; color:var(--text-muted); font-weight:500; }
        .table-card, .form-card { background:var(--card-bg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); overflow:hidden; }
        .form-card { padding:32px; max-width:760px; }
        .card-header { padding:20px 24px; border-bottom:1px solid var(--border); font-size:17px; font-weight:700; }
        table { width:100%; border-collapse:collapse; text-align:left; }
        th { background:#f8fafc; padding:14px 24px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted); border-bottom:1px solid var(--border); }
        td { padding:16px 24px; font-size:14px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        .badge-type { padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; }
        .badge-compound { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        .badge-simple { background:#fefce8; color:#854d0e; border:1px solid #fef08a; }
        .badge-active { background:#ecfdf5; color:var(--success); padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-inactive { background:#f1f5f9; color:#64748b; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .actions-cell { display:flex; gap:8px; align-items:center; }
        .btn-action { padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:none; background:#f1f5f9; color:var(--text-main); }
        .btn-deactivate { background:#fef2f2; color:var(--danger); }
        .btn-activate { background:#ecfdf5; color:var(--success); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:4px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; }
        .form-control { width:100%; padding:11px 14px; border-radius:var(--radius-sm); border:1px solid var(--border); font-size:14px; background:#fff; }
        .form-control:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(67,97,238,.15); }
        .form-hint { font-size:12px; color:var(--text-muted); margin-top:4px; }
        .preview-box { background:linear-gradient(135deg,#f0fdf4,#e0f2fe); border:1px solid #bae6fd; border-radius:var(--radius-md); padding:20px; margin-bottom:24px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:16px; }
        .preview-box p { font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase; }
        .preview-box h3 { font-size:20px; font-weight:800; color:var(--primary-dark); margin-top:2px; }
        .modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,.6); display:none; align-items:center; justify-content:center; z-index:1000; padding:20px; }
        .modal-card { background:#fff; width:100%; max-width:600px; border-radius:var(--radius-lg); box-shadow:var(--shadow-lg); overflow:hidden; }
        .modal-header { padding:20px 28px; background:#f8fafc; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; }
        .modal-header h3 { font-size:18px; }
        .modal-close { background:transparent; border:none; font-size:22px; cursor:pointer; }
        .modal-body { padding:28px; max-height:75vh; overflow-y:auto; }
        .modal-footer { padding:16px 28px; background:#f8fafc; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:12px; }
        .empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
        .info-row { display:flex; justify-content:space-between; padding:11px 0; border-bottom:1px solid var(--border); font-size:14px; }
        .info-row:last-child { border-bottom:none; }
        .info-label { color:var(--text-muted); }
        .info-val { font-weight:700; text-align:right; }
        @media print { .navbar, .no-print { display:none !important; } body { background:#fff; } }
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
                <span><?= esc($username ?? '') ?></span>
                <span class="badge"><?= esc($role ?? '') ?></span>
            </div>
            <?php if (in_array($role, ['Branch Manager', 'SUPER ADMIN'])): ?>
                <a href="<?= base_url('fd/products') ?>" class="btn-nav">FD Products</a>
                <a href="<?= base_url('fd/interest-logs') ?>" class="btn-nav">Interest Logs</a>
            <?php endif; ?>
            <?php if (in_array($role, ['Teller', 'SUPER ADMIN'])): ?>
                <a href="<?= base_url('fd/open') ?>" class="btn-nav">Open FD</a>
            <?php endif; ?>
            <a href="<?= base_url('fd/calculator') ?>" class="btn-nav">Calculator</a>
            <a href="<?= base_url('dashboard') ?>" class="btn-nav">Dashboard</a>
            <a href="<?= base_url('logout') ?>" class="btn-nav btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success auto-dismiss"><span><?= esc(session()->getFlashdata('success')) ?></span>
                <button type="button" class="alert-close" onclick="dismissAlert(this.parentElement)">&times;</button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger auto-dismiss"><span><?= esc(session()->getFlashdata('error')) ?></span>
                <button type="button" class="alert-close" onclick="dismissAlert(this.parentElement)">&times;</button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="padding-left:18px;">
                    <?php foreach ((array) session()->getFlashdata('errors') as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>

    <script>
        function dismissAlert(el) { if (!el) return; el.classList.add('fade-out'); setTimeout(() => el.remove(), 450); }
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.auto-dismiss').forEach(a => setTimeout(() => dismissAlert(a), 4000));
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>