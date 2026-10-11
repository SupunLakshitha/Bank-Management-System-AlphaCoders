<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Submit Loan Application</title>
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
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
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

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
        }

        .nav-brand h2 {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-pill {
            background: rgba(255,255,255,0.08);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .user-pill .badge {
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .btn-nav {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-dash { background: rgba(255,255,255,0.1); color: white; }
        .btn-dash:hover { background: rgba(255,255,255,0.2); }
        .btn-logout { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
        .btn-logout:hover { background: var(--danger); color: white; }

        /* Container */
        .container {
            max-width: 1020px;
            width: 100%;
            margin: 32px auto;
            padding: 0 24px;
        }

        /* Wizard Header */
        .wizard-header {
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .wizard-header h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .wizard-header p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        /* Stepper Progress Bar */
        .stepper-nav {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }

        .step-item {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .step-item.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
            background: #ffffff;
        }

        .step-item.completed {
            border-color: var(--success);
            background: #f0fdf4;
        }

        .step-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step-item.active .step-badge {
            background: var(--primary);
            color: white;
        }

        .step-item.completed .step-badge {
            background: var(--success);
            color: white;
        }

        .step-info h4 {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
        }

        .step-info p {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* Form Card */
        .form-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 36px;
            margin-bottom: 30px;
        }

        .step-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .step-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-heading {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .section-subheading {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-main);
            background: white;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .form-hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Live EMI Preview Box */
        .emi-preview-box {
            background: linear-gradient(135deg, #f0fdf4, #e0f2fe);
            border: 1px solid #bae6fd;
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .emi-item p {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
        }

        .emi-item h3 {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-dark);
            margin-top: 2px;
        }

        /* File Upload Dropzones */
        .upload-card {
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
            text-align: center;
            background: #fafafa;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .upload-card:hover {
            border-color: var(--primary);
            background: #f0f7ff;
        }

        .upload-icon {
            width: 44px;
            height: 44px;
            background: #eff6ff;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
        }

        .upload-card input[type="file"] {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-selected-text {
            font-size: 12px;
            font-weight: 600;
            color: var(--success);
            margin-top: 6px;
        }

        /* Review Summary Box */
        .review-box {
            background: #f8fafc;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 24px;
        }

        .review-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .review-row:last-child {
            border-bottom: none;
        }

        .review-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .review-value {
            color: var(--text-main);
            font-weight: 700;
        }

        /* Wizard Footer Buttons */
        .wizard-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-prev {
            background: #f1f5f9;
            color: var(--text-main);
        }
        .btn-prev:hover { background: #e2e8f0; }

        .btn-next, .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(67, 97, 238, 0.35);
        }
        .btn-next:hover, .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.45);
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            margin-bottom: 24px;
        }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="navbar">
        <a href="<?= base_url('dashboard') ?>" class="nav-brand">
            <div class="brand-logo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="21" x2="21" y2="21"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <polygon points="12 2 3 7 21 7 12 2"></polygon>
                    <line x1="5" y1="10" x2="5" y2="21"></line>
                    <line x1="9" y1="10" x2="9" y2="21"></line>
                    <line x1="15" y1="10" x2="15" y2="21"></line>
                    <line x1="19" y1="10" x2="19" y2="21"></line>
                </svg>
            </div>
            <h2>Bank Management</h2>
        </a>
        <div class="nav-actions">
            <div class="user-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span><?= esc($username ?? 'Loan Officer') ?></span>
                <span class="badge"><?= esc($role ?? 'Loan Officer') ?></span>
            </div>
            <a href="<?= base_url('loans/applications') ?>" class="btn-nav btn-dash">Applications Queue</a>
            <a href="<?= base_url('dashboard') ?>" class="btn-nav btn-dash">Dashboard</a>
            <a href="<?= base_url('logout') ?>" class="btn-nav btn-logout">Logout</a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">

        <!-- Wizard Header -->
        <div class="wizard-header">
            <div>
                <h1>Submit Loan Application</h1>
                <p>Multi-step application submission for credit evaluation & Branch Manager approval.</p>
            </div>
            <a href="<?= base_url('loans/applications') ?>" class="btn btn-prev">&larr; Back to Applications</a>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="padding-left: 20px;">
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Stepper Navigation -->
        <div class="stepper-nav">
            <div class="step-item active" id="stepIndicator1">
                <div class="step-badge">1</div>
                <div class="step-info">
                    <h4>Loan Details</h4>
                    <p>Customer & Terms</p>
                </div>
            </div>
            <div class="step-item" id="stepIndicator2">
                <div class="step-badge">2</div>
                <div class="step-info">
                    <h4>Collateral</h4>
                    <p>Security & Assets</p>
                </div>
            </div>
            <div class="step-item" id="stepIndicator3">
                <div class="step-badge">3</div>
                <div class="step-info">
                    <h4>Guarantors</h4>
                    <p>Primary Guarantor</p>
                </div>
            </div>
            <div class="step-item" id="stepIndicator4">
                <div class="step-badge">4</div>
                <div class="step-info">
                    <h4>Documents & Review</h4>
                    <p>Upload & Submit</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <form id="loanForm" method="POST" action="<?= base_url('loans/apply') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- STEP 1: Loan & Customer Details -->
                <div class="step-section active" id="stepSection1">
                    <h3 class="section-heading">Step 1: Customer Profile & Loan Terms</h3>
                    <p class="section-subheading">Select the applicant customer and define the requested loan amount and tenure.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_id">Select Customer *</label>
                            <select name="customer_id" id="customer_id" class="form-control" required onchange="updateCustomerDetails()">
                                <option value="">-- Choose Customer --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" 
                                            data-name="<?= esc($c['full_name']) ?>" 
                                            data-nic="<?= esc($c['nic'] ?? 'N/A') ?>" 
                                            data-phone="<?= esc($c['phone'] ?? 'N/A') ?>">
                                        <?= esc($c['customer_id']) ?> - <?= esc($c['full_name']) ?> (<?= esc($c['branch_name'] ?? 'No branch') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="loan_product_id">Loan Product Offering *</label>
                            <select name="loan_product_id" id="loan_product_id" class="form-control" required onchange="updateProductDetails()">
                                <option value="">-- Choose Loan Scheme --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" 
                                            data-rate="<?= $p['interest_rate'] ?>" 
                                            data-type="<?= $p['interest_type'] ?>"
                                            data-min-amount="<?= $p['min_amount'] ?>" 
                                            data-max-amount="<?= $p['max_amount'] ?>" 
                                            data-min-tenure="<?= $p['min_tenure_months'] ?>" 
                                            data-max-tenure="<?= $p['max_tenure_months'] ?>"
                                            data-name="<?= esc($p['name']) ?>">
                                        <?= esc($p['name']) ?> (<?= number_format($p['interest_rate'], 2) ?>% p.a. - <?= ucfirst($p['interest_type']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-hint" id="productLimitsHint">Select a product to view limits & base rate.</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount_requested">Requested Loan Amount (LKR) *</label>
                            <input type="number" step="1000" name="amount_requested" id="amount_requested" class="form-control" placeholder="e.g. 500000" required oninput="calculateLiveEmi()">
                        </div>

                        <div class="form-group">
                            <label for="tenure_months">Tenure (Months) *</label>
                            <input type="number" min="1" name="tenure_months" id="tenure_months" class="form-control" placeholder="e.g. 36" required oninput="calculateLiveEmi()">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="monthly_income">Applicant Monthly Income (LKR) *</label>
                            <input type="number" step="1000" name="monthly_income" id="monthly_income" class="form-control" placeholder="e.g. 120000" required>
                        </div>

                        <div class="form-group">
                            <label for="purpose">Loan Purpose</label>
                            <input type="text" name="purpose" id="purpose" class="form-control" placeholder="e.g. Home Renovation, Commercial Vehicle, Working Capital">
                        </div>
                    </div>

                    <!-- Live EMI Preview Calculation -->
                    <div class="emi-preview-box" id="emiPreviewContainer">
                        <div class="emi-item">
                            <p>Base Interest Rate</p>
                            <h3 id="previewInterestRate">--%</h3>
                        </div>
                        <div class="emi-item">
                            <p>Estimated Monthly Installment (EMI)</p>
                            <h3 id="previewEmiAmount" style="color: var(--primary);">Rs. 0.00</h3>
                        </div>
                        <div class="emi-item">
                            <p>Total Estimated Payable</p>
                            <h3 id="previewTotalPayable">Rs. 0.00</h3>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Collateral Details -->
                <div class="step-section" id="stepSection2">
                    <h3 class="section-heading">Step 2: Collateral & Security Information</h3>
                    <p class="section-subheading">Provide asset details pledged as security for this credit facility.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="collateral_type">Collateral Type</label>
                            <select name="collateral_type" id="collateral_type" class="form-control">
                                <option value="None">No Collateral (Unsecured)</option>
                                <option value="Property Deed">Property / Land Title Deed</option>
                                <option value="Vehicle Title">Vehicle Registration / Absolute Ownership</option>
                                <option value="Fixed Deposit Lien">Fixed Deposit Lien / Cash Deposit</option>
                                <option value="Gold">Gold Articles / Jewelry</option>
                                <option value="Other Asset">Other Asset / Machinery</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="collateral_value">Estimated Collateral Value (LKR)</label>
                            <input type="number" step="1000" name="collateral_value" id="collateral_value" class="form-control" placeholder="e.g. 1500000">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="collateral_description">Asset Description & Registration / Deed Details</label>
                        <textarea name="collateral_description" id="collateral_description" class="form-control" rows="3" placeholder="Enter Deed No., Registration Plate No., Property Location, or Certificate Details..."></textarea>
                    </div>
                </div>

                <!-- STEP 3: Guarantors Information -->
                <div class="step-section" id="stepSection3">
                    <h3 class="section-heading">Step 3: Primary Guarantor Information</h3>
                    <p class="section-subheading">Information of the individual guaranteeing repayment in case of default.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="guarantor_name">Guarantor Full Name</label>
                            <input type="text" name="guarantor_name" id="guarantor_name" class="form-control" placeholder="e.g. A.B. Suneth Fernando">
                        </div>

                        <div class="form-group">
                            <label for="guarantor_nic">Guarantor NIC / Passport No.</label>
                            <input type="text" name="guarantor_nic" id="guarantor_nic" class="form-control" placeholder="e.g. 198823456789">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="guarantor_phone">Contact Phone Number</label>
                            <input type="tel" name="guarantor_phone" id="guarantor_phone" class="form-control" placeholder="e.g. 0779876543">
                        </div>

                        <div class="form-group">
                            <label for="guarantor_relationship">Relationship to Applicant</label>
                            <input type="text" name="guarantor_relationship" id="guarantor_relationship" class="form-control" placeholder="e.g. Spouse, Brother, Business Partner">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="guarantor_address">Guarantor Residential Address</label>
                        <textarea name="guarantor_address" id="guarantor_address" class="form-control" rows="2" placeholder="Full permanent residential address..."></textarea>
                    </div>
                </div>

                <!-- STEP 4: Documents Upload & Final Review -->
                <div class="step-section" id="stepSection4">
                    <h3 class="section-heading">Step 4: Document Uploads & Final Confirmation</h3>
                    <p class="section-subheading">Upload mandatory KYC and income verification documents before submitting for review.</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Customer KYC Document (NIC / Passport Copy)</label>
                            <div class="upload-card">
                                <div class="upload-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                </div>
                                <strong>Upload KYC Identity Proof</strong>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">PDF, PNG, JPG up to 5MB</p>
                                <input type="file" name="kyc_doc" id="kyc_doc" accept=".pdf,.png,.jpg,.jpeg" onchange="displayFileName('kyc_doc', 'kycFileName')">
                                <div class="file-selected-text" id="kycFileName"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Proof of Income (Salary Slip / 3-Month Statement)</label>
                            <div class="upload-card">
                                <div class="upload-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </div>
                                <strong>Upload Income Statement</strong>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">PDF, PNG, JPG up to 5MB</p>
                                <input type="file" name="income_doc" id="income_doc" accept=".pdf,.png,.jpg,.jpeg" onchange="displayFileName('income_doc', 'incomeFileName')">
                                <div class="file-selected-text" id="incomeFileName"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Officer Review Remarks / Recommendation</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Creditworthiness assessment or notes for the Branch Manager..."></textarea>
                    </div>

                    <!-- Live Review Summary Card -->
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Application Summary</h4>
                    <div class="review-box">
                        <div class="review-row">
                            <span class="review-label">Applicant Customer</span>
                            <span class="review-value" id="summaryCustomer">--</span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">Selected Loan Scheme</span>
                            <span class="review-value" id="summaryProduct">--</span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">Requested Loan Principal</span>
                            <span class="review-value" id="summaryAmount">Rs. 0.00</span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">Tenure & Interest Rate</span>
                            <span class="review-value" id="summaryTenureRate">-- Months @ --%</span>
                        </div>
                        <div class="review-row">
                            <span class="review-label">Initial Submission Status</span>
                            <span class="review-value" style="color: var(--warning); font-weight: 800;">Pending Review</span>
                        </div>
                    </div>
                </div>

                <!-- Wizard Navigation Footer -->
                <div class="wizard-footer">
                    <button type="button" class="btn btn-prev" id="btnPrev" onclick="navigateStep(-1)" style="display: none;">
                        &larr; Previous Step
                    </button>
                    <div></div>
                    <button type="button" class="btn btn-next" id="btnNext" onclick="navigateStep(1)">
                        Next Step &rarr;
                    </button>
                    <button type="submit" class="btn btn-submit" id="btnSubmit" style="display: none;">
                        ✓ Submit Application for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function navigateStep(direction) {
            if (direction === 1 && !validateCurrentStep(currentStep)) {
                return;
            }

            currentStep += direction;
            showStep(currentStep);
        }

        function showStep(step) {
            // Toggle Sections
            for (let i = 1; i <= totalSteps; i++) {
                const section = document.getElementById('stepSection' + i);
                const indicator = document.getElementById('stepIndicator' + i);

                if (i === step) {
                    section.classList.add('active');
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else if (i < step) {
                    section.classList.remove('active');
                    indicator.classList.remove('active');
                    indicator.classList.add('completed');
                } else {
                    section.classList.remove('active');
                    indicator.classList.remove('active');
                    indicator.classList.remove('completed');
                }
            }

            // Toggle Navigation Buttons
            document.getElementById('btnPrev').style.display = (step === 1) ? 'none' : 'inline-flex';
            document.getElementById('btnNext').style.display = (step === totalSteps) ? 'none' : 'inline-flex';
            document.getElementById('btnSubmit').style.display = (step === totalSteps) ? 'inline-flex' : 'none';

            if (step === 4) {
                populateSummary();
            }
        }

        function validateCurrentStep(step) {
            if (step === 1) {
                const customer = document.getElementById('customer_id').value;
                const product = document.getElementById('loan_product_id').value;
                const amount = parseFloat(document.getElementById('amount_requested').value);
                const tenure = parseInt(document.getElementById('tenure_months').value);
                const income = parseFloat(document.getElementById('monthly_income').value);

                if (!customer) { alert("Please select an applicant customer."); return false; }
                if (!product) { alert("Please select a loan product offering."); return false; }
                if (!amount || amount <= 0) { alert("Please enter a valid requested loan amount."); return false; }
                if (!tenure || tenure <= 0) { alert("Please enter loan tenure in months."); return false; }
                if (!income || income <= 0) { alert("Please enter customer's monthly income."); return false; }

                const productSelect = document.getElementById('loan_product_id');
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const minAmount = parseFloat(selectedOption.getAttribute('data-min-amount'));
                const maxAmount = parseFloat(selectedOption.getAttribute('data-max-amount'));
                const minTenure = parseInt(selectedOption.getAttribute('data-min-tenure'));
                const maxTenure = parseInt(selectedOption.getAttribute('data-max-tenure'));

                if (amount < minAmount || amount > maxAmount) {
                    alert(`Loan Amount must be between Rs. ${minAmount.toLocaleString()} and Rs. ${maxAmount.toLocaleString()}`);
                    return false;
                }
                if (tenure < minTenure || tenure > maxTenure) {
                    alert(`Tenure must be between ${minTenure} and ${maxTenure} months.`);
                    return false;
                }
            }
            return true;
        }

        function updateProductDetails() {
            const select = document.getElementById('loan_product_id');
            const opt = select.options[select.selectedIndex];
            if (!opt.value) {
                document.getElementById('productLimitsHint').innerText = "Select a product to view limits & base rate.";
                document.getElementById('previewInterestRate').innerText = "--%";
                return;
            }

            const rate = opt.getAttribute('data-rate');
            const minAmt = parseFloat(opt.getAttribute('data-min-amount')).toLocaleString();
            const maxAmt = parseFloat(opt.getAttribute('data-max-amount')).toLocaleString();
            const minTenure = opt.getAttribute('data-min-tenure');
            const maxTenure = opt.getAttribute('data-max-tenure');

            document.getElementById('productLimitsHint').innerText = `Allowed: Rs. ${minAmt} - Rs. ${maxAmt} | Tenure: ${minTenure} - ${maxTenure} Months | Rate: ${rate}%`;
            document.getElementById('previewInterestRate').innerText = `${rate}% p.a.`;

            calculateLiveEmi();
        }

        function calculateLiveEmi() {
            const amount = parseFloat(document.getElementById('amount_requested').value);
            const tenure = parseInt(document.getElementById('tenure_months').value);
            const select = document.getElementById('loan_product_id');
            const opt = select.options[select.selectedIndex];

            if (!amount || !tenure || !opt.value) {
                document.getElementById('previewEmiAmount').innerText = "Rs. 0.00";
                document.getElementById('previewTotalPayable').innerText = "Rs. 0.00";
                return;
            }

            const annualRate = parseFloat(opt.getAttribute('data-rate'));
            const type = opt.getAttribute('data-type');

            let monthlyEmi = 0;
            let totalPayable = 0;

            if (type === 'simple') {
                // Flat simple interest
                const totalInterest = amount * (annualRate / 100) * (tenure / 12);
                totalPayable = amount + totalInterest;
                monthlyEmi = totalPayable / tenure;
            } else {
                // Reducing Balance EMI Formula
                const r = (annualRate / 12) / 100;
                const emi = (amount * r * Math.pow(1 + r, tenure)) / (Math.pow(1 + r, tenure) - 1);
                monthlyEmi = emi;
                totalPayable = emi * tenure;
            }

            document.getElementById('previewEmiAmount').innerText = "Rs. " + monthlyEmi.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('previewTotalPayable').innerText = "Rs. " + totalPayable.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function displayFileName(inputId, targetId) {
            const input = document.getElementById(inputId);
            if (input.files && input.files.length > 0) {
                document.getElementById(targetId).innerText = "Selected: " + input.files[0].name;
            }
        }

        function populateSummary() {
            const customerSelect = document.getElementById('customer_id');
            const productSelect = document.getElementById('loan_product_id');
            const amount = parseFloat(document.getElementById('amount_requested').value || 0);
            const tenure = document.getElementById('tenure_months').value;

            const custText = customerSelect.options[customerSelect.selectedIndex]?.text || '--';
            const prodText = productSelect.options[productSelect.selectedIndex]?.getAttribute('data-name') || '--';
            const rate = productSelect.options[productSelect.selectedIndex]?.getAttribute('data-rate') || '--';

            document.getElementById('summaryCustomer').innerText = custText;
            document.getElementById('summaryProduct').innerText = prodText;
            document.getElementById('summaryAmount').innerText = "Rs. " + amount.toLocaleString('en-US', { minimumFractionDigits: 2 });
            document.getElementById('summaryTenureRate').innerText = `${tenure} Months @ ${rate}% p.a.`;
        }
    </script>
</body>
</html>
