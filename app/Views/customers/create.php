<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Onboard Customer') ?></title>
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

        .container { max-width: 900px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 16px; }

        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.04); padding: 36px; }
        .card-header { margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 16px; }
        .card-header h1 { font-size: 22px; font-weight: 800; color: #1e1e2f; }
        .card-header p { font-size: 14px; color: var(--text-muted); margin-top: 4px; }

        .form-section { margin-bottom: 28px; }
        .section-title { font-size: 14px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group.full { grid-column: span 2; }
        .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #1e1e2f; }
        .form-label span.req { color: var(--danger); }
        .form-control, .form-select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            background: #fff;
            outline: none;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15); }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 13px 28px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(67, 97, 238, 0.35);
        }
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
            <a href="<?= base_url('logout') ?>" class="nav-link" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <main class="container">
        <a href="<?= base_url('customers') ?>" class="back-link">&larr; Back to Customer Directory</a>

        <div class="card">
            <div class="card-header">
                <h1>Onboard New Customer (KYC Registration)</h1>
                <p>Register a verified or pending customer profile according to standard banking KYC guidelines.</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('customers/create') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="section-title">1. Essential Identity & KYC Profile</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Customer ID <span class="req">*</span></label>
                            <input type="text" name="customer_id" class="form-control" value="<?= esc(old('customer_id', $generatedId)) ?>" required>
                            <small style="color: var(--text-muted); font-size: 11px;">Unique banking customer identifier (e.g. C001, C002).</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">KYC Status</label>
                            <select name="kyc_status" class="form-select">
                                <option value="Verified" <?= old('kyc_status') === 'Verified' ? 'selected' : '' ?>>Verified (Documentation Complete)</option>
                                <option value="Pending" <?= old('kyc_status', 'Pending') === 'Pending' ? 'selected' : '' ?>>Pending (Verification in Progress)</option>
                                <option value="Rejected" <?= old('kyc_status') === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. Kamal Perera" value="<?= esc(old('full_name')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIC / Passport Number <span class="req">*</span></label>
                            <input type="text" name="nic" class="form-control" placeholder="e.g. 198512345678 or N1234567" value="<?= esc(old('nic')) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="<?= esc(old('dob')) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Occupation / Source of Income</label>
                            <input type="text" name="occupation" class="form-control" placeholder="e.g. Software Engineer, Business Owner" value="<?= esc(old('occupation')) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">2. Contact Details & Branch Allocation</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Telephone / Mobile</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 0771234567" value="<?= esc(old('phone')) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. kamal@example.com" value="<?= esc(old('email')) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Associated Bank Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Select Branch</option>
                                <?php foreach($branches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= old('branch_id') == $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?> (<?= esc($b['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Permanent Residential Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Street address, City"><?= esc(old('address')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">3. Digital Banking Login Credentials (Optional)</div>
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 18px; margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; cursor: pointer; margin-bottom: 12px;">
                            <input type="checkbox" name="create_login" value="1" id="createLoginCheck" onchange="document.getElementById('loginFields').style.display = this.checked ? 'grid' : 'none'">
                            Create Customer Portal User Login
                        </label>
                        <div id="loginFields" class="form-row" style="display: none; margin-bottom: 0;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Portal Username</label>
                                <input type="text" name="username" class="form-control" placeholder="e.g. customer_kamal">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Portal Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Default: Customer@123">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 30px; align-items: center;">
                    <button type="submit" class="btn-submit">Complete Customer Onboarding &rarr;</button>
                    <a href="<?= base_url('customers') ?>" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
