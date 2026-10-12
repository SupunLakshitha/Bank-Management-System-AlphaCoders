<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Process Deposit') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
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
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), #4cc9f0); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
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

        .lookup-wrap { display: flex; gap: 8px; }
        .btn-lookup { padding: 11px 18px; background: var(--primary); color: white; border: none; border-radius: var(--radius-sm); font-weight: 600; cursor: pointer; font-size: 13px; }

        .account-preview-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 24px;
            display: none;
        }

        .balance-calc-card {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
            margin: 20px 0;
        }
        .calc-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .calc-total { border-top: 2px dashed var(--border); margin-top: 8px; padding-top: 10px; font-size: 16px; font-weight: 800; color: #10b981; }

        .btn-submit { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 13px 28px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); }
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
            <a href="<?= base_url('withdrawals') ?>" class="nav-link">Withdrawals</a>
            <a href="<?= base_url('logout') ?>" class="nav-link" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <main class="container">
        <a href="<?= base_url('deposits') ?>" class="back-link">&larr; Back to Deposits Journal</a>

        <div class="card">
            <div class="card-header">
                <h1>Teller Deposit Terminal</h1>
                <p>Execute an immediate credit deposit into a verified bank account with atomic balance updating.</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('deposits/create') ?>" method="post" id="depositForm">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Destination Account Number <span style="color: var(--danger);">*</span></label>
                    <div class="lookup-wrap">
                        <input type="text" name="account_no" id="accountNoInput" class="form-control" placeholder="Enter 10-digit account number (e.g. 1000012345)" value="<?= esc(old('account_no', $selectedAccount['account_no'] ?? '')) ?>" required>
                        <button type="button" class="btn-lookup" onclick="lookupAccount()">Lookup</button>
                    </div>
                </div>

                <div id="accountPreview" class="account-preview-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #166534;">Verified Account Holder</div>
                            <h3 id="prevCustomerName" style="color: #14532d; font-size: 18px; margin-top: 2px;"></h3>
                            <div id="prevDetails" style="font-size: 12px; color: #166534; margin-top: 2px;"></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #166534;">Current Balance</div>
                            <h2 id="prevBalance" style="color: #15803d; font-size: 22px; font-weight: 800;">Rs. 0.00</h2>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Deposit Amount (Rs.) <span style="color: var(--danger);">*</span></label>
                        <input type="number" step="0.01" min="1" name="amount" id="depositAmount" class="form-control" placeholder="e.g. 50000.00" value="<?= esc(old('amount')) ?>" required oninput="calculateBalance()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deposit Method</label>
                        <select name="deposit_method" class="form-select">
                            <option value="Cash" <?= old('deposit_method') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="Cheque" <?= old('deposit_method') === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                            <option value="Online Transfer" <?= old('deposit_method') === 'Online Transfer' ? 'selected' : '' ?>>Online Transfer</option>
                            <option value="Other" <?= old('deposit_method') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Dual-Entry Balance Calculator -->
                <div class="balance-calc-card">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Deposit Transaction Preview</div>
                    <div class="calc-row">
                        <span>Previous Account Balance:</span>
                        <strong id="calcPrev">Rs. 0.00</strong>
                    </div>
                    <div class="calc-row">
                        <span>Deposit Amount (+) :</span>
                        <strong id="calcAmount" style="color: #10b981;">Rs. 0.00</strong>
                    </div>
                    <div class="calc-row calc-total">
                        <span>New Balance After Credit:</span>
                        <span id="calcNew">Rs. 0.00</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Reference Number (Optional)</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Cheque number or external ref" value="<?= esc(old('reference_number')) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teller Remarks / Narration</label>
                        <input type="text" name="remarks" class="form-control" placeholder="e.g. Over-the-counter cash deposit" value="<?= esc(old('remarks')) ?>">
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 30px;">
                    <button type="submit" class="btn-submit">Confirm &amp; Post Deposit &rarr;</button>
                    <a href="<?= base_url('deposits') ?>" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        let currentAccountBalance = 0;

        async function lookupAccount() {
            const accNo = document.getElementById('accountNoInput').value.trim();
            if (!accNo) return;

            try {
                const res = await fetch('<?= base_url('api/accounts/lookup') ?>/' + encodeURIComponent(accNo));
                const json = await res.json();

                if (json.status === 'success') {
                    const data = json.data;
                    currentAccountBalance = data.balance;
                    document.getElementById('prevCustomerName').textContent = data.customer_name + ' (' + data.customer_code + ')';
                    document.getElementById('prevDetails').textContent = data.account_type + ' • NIC: ' + (data.customer_nic || 'N/A') + ' • Status: ' + data.status.toUpperCase();
                    document.getElementById('prevBalance').textContent = 'Rs. ' + Number(data.balance).toLocaleString(undefined, {minimumFractionDigits: 2});
                    document.getElementById('accountPreview').style.display = 'block';
                    calculateBalance();
                } else {
                    alert(json.message || 'Account not found.');
                    document.getElementById('accountPreview').style.display = 'none';
                }
            } catch (e) {
                console.error(e);
            }
        }

        function calculateBalance() {
            const depositAmt = parseFloat(document.getElementById('depositAmount').value) || 0;
            const newBal = currentAccountBalance + depositAmt;

            document.getElementById('calcPrev').textContent = 'Rs. ' + currentAccountBalance.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('calcAmount').textContent = '+ Rs. ' + depositAmt.toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('calcNew').textContent = 'Rs. ' + newBal.toLocaleString(undefined, {minimumFractionDigits: 2});
        }

        // Auto trigger if initial value
        if (document.getElementById('accountNoInput').value) {
            lookupAccount();
        }
    </script>
</body>
</html>
