<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Branches</title>
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
        .card h1 { font-size: 24px; margin-bottom: 8px; color: #1e1e2f; }
        .card p { color: #666; line-height: 1.5; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .branch-card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-top: 4px solid #4facfe; display: flex; flex-direction: column; gap: 10px; }
        .branch-card h3 { color: #1e1e2f; }
        .code { display: inline-block; background: #e6f7ff; color: #096dd9; border-radius: 4px; padding: 2px 8px; font-size: 12px; font-weight: 600; }
        .meta { color: #666; font-size: 13px; }
        .counts { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; font-size: 13px; color: #444; }
        .counts strong { color: #1e1e2f; }
        .btn { display: inline-block; padding: 8px 14px; background: #4361ee; color: white; text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 600; width: fit-content; }
        .form-row { display: grid; grid-template-columns: 1.4fr 0.6fr 1.4fr auto; gap: 12px; align-items: end; }
        label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #d9d9e3; border-radius: 8px; }
        button { padding: 10px 16px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-error { background: rgba(255, 77, 79, 0.1); border: 1px solid #ff4d4f; color: #cf1322; }
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #047857; }
        @media (max-width: 800px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>BMS Branches</h2>
        <div class="nav-links">
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
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <ul style="padding-left: 18px;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <h1>Branch Management</h1>
            <p>Every customer, account, employee, transaction, and loan belongs to one branch. Colombo, Kandy, Kegalle, and Galle are the operating locations.</p>
        </div>

        <?php if ($role === 'SUPER ADMIN'): ?>
            <div class="card">
                <h3 style="margin-bottom: 14px;">Add a branch</h3>
                <form action="<?= base_url('branches') ?>" method="post">
                    <div class="form-row">
                        <div>
                            <label for="name">Branch name</label>
                            <input type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required>
                        </div>
                        <div>
                            <label for="code">Code</label>
                            <input type="text" id="code" name="code" value="<?= esc(old('code')) ?>" maxlength="10" required>
                        </div>
                        <div>
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="<?= esc(old('location')) ?>" required>
                        </div>
                        <button type="submit">Add branch</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="grid">
            <?php foreach ($branches as $branch): ?>
                <article class="branch-card">
                    <div>
                        <span class="code"><?= esc($branch['code']) ?></span>
                        <h3 style="margin-top: 8px;"><?= esc($branch['name']) ?></h3>
                        <p class="meta"><?= esc($branch['location']) ?></p>
                    </div>
                    <div class="counts">
                        <span>Employees <strong><?= (int) $branch['employee_count'] ?></strong></span>
                        <span>Customers <strong><?= (int) $branch['customer_count'] ?></strong></span>
                        <span>Accounts <strong><?= (int) $branch['account_count'] ?></strong></span>
                        <span>Transactions <strong><?= (int) $branch['transaction_count'] ?></strong></span>
                        <span>Loans <strong><?= (int) $branch['loan_count'] ?></strong></span>
                    </div>
                    <a class="btn" href="<?= base_url('branches/' . $branch['id']) ?>">Open branch</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
