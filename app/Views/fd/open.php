<?= $this->extend('fd/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1>Open Fixed Deposit</h1>
        <p>Open an FD for a customer. The principal is deducted from their savings account.</p>
    </div>
</div>

<?php if (! $accountsReady): ?>
    <div class="alert alert-warning">The Accounts module is not available yet, so FDs cannot be opened. This page will work once the accounts table is merged.</div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="<?= base_url('fd/open') ?>">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label>Customer *</label>
                <select name="customer_id" id="customer_id" class="form-control" required onchange="filterAccounts()">
                    <option value="">-- Choose Customer --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= old('customer_id') == $c['id'] ? 'selected' : '' ?>>
                            <?= esc($c['customer_id'] ?? $c['id']) ?> - <?= esc($c['full_name'] ?? 'Customer') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Source Savings Account *</label>
                <select name="account_id" id="account_id" class="form-control" required onchange="calc()">
                    <option value="">-- Choose Account --</option>
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>" data-customer="<?= $a['customer_id'] ?>" data-balance="<?= $a['balance'] ?>">
                            <?= esc($a['account_no']) ?> (Rs. <?= number_format($a['balance'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>FD Product *</label>
                <select name="fd_product_id" id="fd_product_id" class="form-control" required onchange="calc()">
                    <option value="">-- Choose Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" data-rate="<?= $p['interest_rate'] ?>" data-type="<?= $p['interest_type'] ?>"
                                data-tenure="<?= $p['tenure_months'] ?>" data-min="<?= $p['min_amount'] ?>" data-times="<?= $p['compounding_per_year'] ?>">
                            <?= esc($p['name']) ?> (<?= number_format($p['interest_rate'], 2) ?>%, <?= $p['tenure_months'] ?> mo)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint" id="productHint">Select a product to see its minimum amount.</div>
            </div>
            <div class="form-group">
                <label>Deposit Amount (LKR) *</label>
                <input type="number" step="any" min="1" name="amount" id="amount" class="form-control" value="<?= esc(old('amount')) ?>" required oninput="calc()">
            </div>
        </div>

        <div class="preview-box">
            <div><p>Estimated Interest</p><h3 id="pvInterest">Rs. 0.00</h3></div>
            <div><p>Estimated Maturity Amount</p><h3 id="pvMaturity">Rs. 0.00</h3></div>
        </div>
        <div class="form-hint" style="margin-bottom:18px;">This is an estimate. The certificate shows the exact figures.</div>

        <button type="submit" class="btn-primary" <?= $accountsReady ? '' : 'disabled style="opacity:.5;cursor:not-allowed;"' ?>>Open Fixed Deposit</button>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function filterAccounts() {
        const cid = document.getElementById('customer_id').value;
        const sel = document.getElementById('account_id');
        sel.value = '';
        [...sel.options].forEach(o => { if (o.value) o.hidden = (o.dataset.customer !== cid); });
    }
    function money(n) { return 'Rs. ' + n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function calc() {
        const opt = document.getElementById('fd_product_id').selectedOptions[0];
        const amt = parseFloat(document.getElementById('amount').value) || 0;
        if (!opt || !opt.value) return;
        const rate = parseFloat(opt.dataset.rate) / 100, years = opt.dataset.tenure / 12, n = parseInt(opt.dataset.times);
        document.getElementById('productHint').innerText = 'Minimum amount: ' + money(parseFloat(opt.dataset.min));
        const interest = opt.dataset.type === 'compound'
            ? amt * Math.pow(1 + rate / n, n * years) - amt
            : amt * rate * years;
        document.getElementById('pvInterest').innerText = money(interest);
        document.getElementById('pvMaturity').innerText = money(amt + interest);
    }
    filterAccounts();
</script>
<?= $this->endSection() ?>