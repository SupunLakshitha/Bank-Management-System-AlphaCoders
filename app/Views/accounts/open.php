<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Open Account') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4cc9f0;
            --success: #10b981;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
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

        .container { max-width: 860px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.04); padding: 36px; }
        .card-header { margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 16px; }
        .card-header h1 { font-size: 22px; font-weight: 800; color: #1e1e2f; }
        .card-header p { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #1e1e2f; }
        .form-control, .form-select { width: 100%; padding: 11px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: #fff; outline: none; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15); }

        .info-pill { background: #e0f2fe; border: 1px solid #bae6fd; border-radius: var(--radius-sm); padding: 14px 18px; margin-bottom: 20px; font-size: 13px; color: #0369a1; }

        .btn-submit { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border: none; padding: 13px 28px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(67, 97, 238, 0.35); }
        .btn-submit:hover { transform: translateY(-1px); }
        .btn-cancel { padding: 13px 22px; background: #f1f5f9; color: var(--text-main); border: 1px solid var(--border); border-radius: var(--radius-sm); text-decoration: none; font-size: 14px; font-weight: 600; }

        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
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
            <a href="<?= base_url('logout') ?>" class="nav-link" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <main class="container">
        <a href="<?= base_url('accounts') ?>" class="back-link">&larr; Back to Accounts List</a>

        <div class="card">
            <div class="card-header">
                <h1>Open New Bank Account</h1>
                <p>Create a savings, current, business, or fixed deposit source facility for a registered customer.</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('accounts/open') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Select Customer <span style="color: var(--danger);">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Choose Customer --</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($selectedCustomer) && $selectedCustomer['id'] == $c['id']) || old('customer_id') == $c['id'] ? 'selected' : '' ?>>
                                [<?= esc($c['customer_id']) ?>] <?= esc($c['full_name']) ?> &mdash; NIC: <?= esc($c['nic'] ?? 'N/A') ?> (KYC: <?= esc($c['kyc_status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Account Facility Type <span style="color: var(--danger);">*</span></label>
                        <select name="account_type_id" id="accountTypeSelect" class="form-select" required onchange="updateTypeInfo()">
                            <option value="">-- Choose Account Type --</option>
                            <?php foreach($types as $t): ?>
                                <option value="<?= $t['id'] ?>" 
                                    data-rate="<?= esc($t['interest_rate']) ?>" 
                                    data-min="<?= esc($t['min_balance']) ?>"
                                    data-desc="<?= esc($t['description']) ?>"
                                    <?= old('account_type_id') == $t['id'] ? 'selected' : '' ?>>
                                    <?= esc($t['name']) ?> (Min: Rs. <?= number_format($t['min_balance'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Branch Allocation</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Select Branch</option>
                            <?php foreach($branches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= old('branch_id') == $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="typeInfoBox" class="info-pill" style="display: none;">
                    <strong>Account Rules:</strong> <span id="typeDesc"></span><br>
                    <strong>Annual Interest Rate:</strong> <span id="typeRate"></span>% &bull; 
                    <strong>Minimum Balance Requirement:</strong> Rs. <span id="typeMin"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Initial Opening Deposit (Rs.)</label>
                        <input type="number" step="0.01" name="initial_deposit" id="initialDeposit" class="form-control" placeholder="0.00" value="<?= esc(old('initial_deposit', '1000')) ?>">
                        <small style="color: var(--text-muted); font-size: 11px;">Must satisfy the account type's minimum balance requirement.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deposit Method</label>
                        <select name="deposit_method" class="form-select">
                            <option value="Cash" <?= old('deposit_method') === 'Cash' ? 'selected' : '' ?>>Cash Deposit</option>
                            <option value="Cheque" <?= old('deposit_method') === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                            <option value="Online Transfer" <?= old('deposit_method') === 'Online Transfer' ? 'selected' : '' ?>>Online Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Interest Rate (% p.a.)</label>
                    <input type="number" step="0.01" name="interest_rate" id="interestRateInput" class="form-control" placeholder="Leave empty for account type default" value="<?= esc(old('interest_rate')) ?>">
                </div>

                <div style="display: flex; gap: 12px; margin-top: 30px;">
                    <button type="submit" class="btn-submit">Generate Account &amp; Credit Deposit &rarr;</button>
                    <a href="<?= base_url('accounts') ?>" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        function updateTypeInfo() {
            const select = document.getElementById('accountTypeSelect');
            const selected = select.options[select.selectedIndex];
            const infoBox = document.getElementById('typeInfoBox');

            if (select.value) {
                document.getElementById('typeDesc').textContent = selected.getAttribute('data-desc') || '';
                document.getElementById('typeRate').textContent = selected.getAttribute('data-rate') || '0.00';
                document.getElementById('typeMin').textContent = Number(selected.getAttribute('data-min') || 0).toLocaleString();
                document.getElementById('interestRateInput').value = selected.getAttribute('data-rate') || '0.00';
                document.getElementById('initialDeposit').value = selected.getAttribute('data-min') || '1000';
                infoBox.style.display = 'block';
            } else {
                infoBox.style.display = 'none';
            }
        }
        if (document.getElementById('accountTypeSelect').value) {
            updateTypeInfo();
        }
    </script>
</body>
</html>
