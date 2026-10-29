<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Employees</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f4f6f9; color: #333; }
        .navbar { background: #2a2a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar h2 { font-weight: 600; font-size: 20px; color: #4facfe; }
        .nav-links { display: flex; gap: 12px; align-items: center; }
        .nav-links a { color: #d6e4ff; text-decoration: none; font-size: 14px; }
        .btn-logout { padding: 8px 15px; background: rgba(255,77,79,.1); border: 1px solid #ff4d4f; border-radius: 5px; color: #ff4d4f !important; }
        .container { max-width: 1100px; margin: 32px auto; padding: 0 20px; }
        .card { background: white; padding: 24px; border-radius: 14px; box-shadow: 0 5px 15px rgba(0,0,0,.05); margin-bottom: 20px; }
        .card h1 { font-size: 24px; margin-bottom: 8px; color: #1e1e2f; }
        .card h3 { margin-bottom: 14px; color: #1e1e2f; }
        .card p { color: #666; line-height: 1.5; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; }
        input, select { width: 100%; padding: 10px 12px; border: 1px solid #d9d9e3; border-radius: 8px; font-size: 14px; background: white; }
        button { padding: 10px 16px; background: #4361ee; color: white; border: 0; border-radius: 8px; font-weight: 600; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { color: #666; font-size: 12px; text-transform: uppercase; }
        .inline { display: flex; gap: 8px; align-items: center; }
        .inline select { padding: 8px; }
        .inline button { padding: 8px 12px; font-size: 13px; }
        .badge { background: #e6f7ff; color: #096dd9; border-radius: 4px; padding: 2px 8px; font-size: 12px; font-weight: 600; }
        .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: #fff1f0; border: 1px solid #ff4d4f; color: #cf1322; }
        .alert-success { background: #f6ffed; border: 1px solid #52c41a; color: #389e0d; }
        @media (max-width: 900px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<nav class="navbar">
    <h2>Bank Management</h2>
    <div class="nav-links">
        <span><?= esc($username) ?> <span class="badge"><?= esc($role) ?></span></span>
        <a href="<?= base_url('dashboard') ?>">Dashboard</a>
        <a href="<?= base_url('branches') ?>">Branches</a>
        <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-error">
            <ul style="padding-left:18px;">
                <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <h1>Employee Management</h1>
        <p>Only a Super Admin can create staff accounts and assign roles. Public sign-up always creates a Customer.</p>
    </div>

    <div class="card">
        <h3>Add employee</h3>
        <form method="post" action="<?= base_url('employees/create') ?>" class="form-row">
            <?= csrf_field() ?>
            <div><label>Username</label><input type="text" name="username" value="<?= esc(old('username')) ?>" required autocomplete="off"></div>
            <div><label>Password (min 8)</label><input type="password" name="password" required autocomplete="new-password"></div>
            <div>
                <label>Role</label>
                <select name="role_id" required>
                    <option value="">-- Role --</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= esc($r['id']) ?>" <?= (string) old('role_id') === (string) $r['id'] ? 'selected' : '' ?>><?= esc($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Branch</label>
                <select name="branch_id" required>
                    <option value="">-- Branch --</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= esc($b['id']) ?>" <?= (string) old('branch_id') === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Create</button>
        </form>
    </div>

    <div class="card">
        <h3>Employees (<?= count($employees) ?>)</h3>
        <table>
            <thead><tr><th>Username</th><th>Current role</th><th>Branch</th><th>Change role / branch</th></tr></thead>
            <tbody>
            <?php foreach ($employees as $e): ?>
                <tr>
                    <td><?= esc($e['username']) ?></td>
                    <td><span class="badge"><?= esc($e['role_name']) ?></span></td>
                    <td><?= esc($e['branch_name'] ?? 'Unassigned') ?></td>
                    <td>
                        <?php if ((int) $e['id'] === $myId): ?>
                            <span style="color:#999;font-size:13px;">(you)</span>
                        <?php else: ?>
                            <form method="post" action="<?= base_url('employees/update/' . $e['id']) ?>" class="inline">
                                <?= csrf_field() ?>
                                <select name="role_id">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= esc($r['id']) ?>" <?= (int) $r['id'] === (int) $e['role_id'] ? 'selected' : '' ?>><?= esc($r['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="branch_id">
                                    <?php foreach ($branches as $b): ?>
                                        <option value="<?= esc($b['id']) ?>" <?= (int) $b['id'] === (int) $e['branch_id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Save</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
