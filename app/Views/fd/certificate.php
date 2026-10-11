<?= $this->extend('fd/layout') ?>

<?= $this->section('content') ?>
<div class="page-header no-print">
    <div><h1>FD Certificate</h1><p>Print this for the customer.</p></div>
    <div style="display:flex;gap:10px;">
        <button class="btn-primary" onclick="window.print()">Print Certificate</button>
        <a href="<?= base_url('fd/open') ?>" class="btn-light">Open Another FD</a>
    </div>
</div>

<div class="form-card" style="border:2px solid var(--primary);">
    <div style="text-align:center;margin-bottom:24px;">
        <p style="color:var(--text-muted);font-weight:600;letter-spacing:2px;">FIXED DEPOSIT CERTIFICATE</p>
        <h2 style="font-size:28px;color:var(--primary-dark);margin-top:6px;"><?= esc($cert['fd_number']) ?></h2>
    </div>
    <div class="info-row"><span class="info-label">Customer ID</span><span class="info-val"><?= (int) $cert['customer_id'] ?></span></div>
    <div class="info-row"><span class="info-label">Source Account</span><span class="info-val"><?= (int) $cert['source_account'] ?></span></div>
    <div class="info-row"><span class="info-label">FD Product</span><span class="info-val"><?= esc($cert['product']) ?></span></div>
    <div class="info-row"><span class="info-label">Principal</span><span class="info-val">Rs. <?= number_format($cert['principal'], 2) ?></span></div>
    <div class="info-row"><span class="info-label">Interest Rate</span><span class="info-val"><?= number_format($cert['interest_rate'], 2) ?>% p.a. (<?= esc(ucfirst($cert['interest_type'])) ?>)</span></div>
    <div class="info-row"><span class="info-label">Tenure</span><span class="info-val"><?= (int) $cert['tenure_months'] ?> Months</span></div>
    <div class="info-row"><span class="info-label">Start Date</span><span class="info-val"><?= esc($cert['start_date']) ?></span></div>
    <div class="info-row"><span class="info-label">Maturity Date</span><span class="info-val"><?= esc($cert['maturity_date']) ?></span></div>
    <div class="info-row"><span class="info-label">Interest at Maturity</span><span class="info-val">Rs. <?= number_format($cert['interest_at_maturity'], 2) ?></span></div>
    <div class="info-row"><span class="info-label">Maturity Amount</span><span class="info-val" style="color:var(--primary);font-size:18px;">Rs. <?= number_format($cert['maturity_amount'], 2) ?></span></div>
    <div class="info-row"><span class="info-label">Status</span><span class="info-val"><?= esc(ucfirst($cert['status'])) ?></span></div>
    <div class="info-row"><span class="info-label">Issued At</span><span class="info-val"><?= esc($cert['issued_at']) ?></span></div>
</div>
<?= $this->endSection() ?>