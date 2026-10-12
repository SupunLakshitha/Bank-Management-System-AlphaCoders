<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - <?= esc($title ?? 'Edit Customer') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4cc9f0;
            --bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius-sm: 8px;
            --radius-lg: 16px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #1e1e2f; padding: 16px 36px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: white; }
        .brand-logo { width: 38px; height: 38px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .nav-links { display: flex; align-items: center; gap: 12px; }
        .nav-link { color: #cbd5e1; text-decoration: none; font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; }
        .container { max-width: 900px; margin: 32px auto; padding: 0 24px; width: 100%; flex: 1; }
        .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .card { background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.04); padding: 36px; }
        .card-header { margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 16px; }
        .card-header h1 { font-size: 22px; font-weight: 800; color: #1e1e2f; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #1e1e2f; }
        .form-control, .form-select { width: 100%; padding: 11px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; background: #fff; outline: none; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15); }
        .btn-submit { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border: none; padding: 13px 28px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 700; cursor: pointer; }
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
        <a href="<?= base_url('customers/view/' . $customer['id']) ?>" class="back-link">&larr; Back to Customer Profile</a>

        <div class="card">
            <div class="card-header">
                <h1>Edit Customer Profile: <?= esc($customer['customer_id']) ?></h1>
                <p>Update customer KYC and demographic contact details.</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('customers/edit/' . $customer['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= esc(old('full_name', $customer['full_name'])) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIC / Passport Number</label>
                        <input type="text" name="nic" class="form-control" value="<?= esc(old('nic', $customer['nic'])) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?= esc(old('dob', $customer['dob'])) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="occupation" class="form-control" value="<?= esc(old('occupation', $customer['occupation'])) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Telephone</label>
                        <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $customer['phone'])) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= esc(old('email', $customer['email'])) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">KYC Status</label>
                        <select name="kyc_status" class="form-select">
                            <option value="Verified" <?= old('kyc_status', $customer['kyc_status']) === 'Verified' ? 'selected' : '' ?>>Verified</option>
                            <option value="Pending" <?= old('kyc_status', $customer['kyc_status']) === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Rejected" <?= old('kyc_status', $customer['kyc_status']) === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Select Branch</option>
                            <?php foreach($branches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= old('branch_id', $customer['branch_id']) == $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= esc(old('address', $customer['address'])) ?></textarea>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <a href="<?= base_url('customers/view/' . $customer['id']) ?>" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
