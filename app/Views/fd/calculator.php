<?= $this->extend('fd/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div><h1>FD Interest Calculator</h1><p>Preview the maturity value before creating an FD. Handles leap years and day-count conventions.</p></div>
</div>

<?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

<div class="form-card">
    <form method="POST" action="<?= base_url('fd/calculator') ?>">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group"><label>Principal (LKR)</label>
            <input type="number" step="any" min="1" name="principal" class="form-control" value="<?= esc($input['principal'] ?? '100000') ?>" required>
            <div class="form-group"><label>Rate (% p.a.)</label>
                <input type="number" step="0.01" name="rate" class="form-control" value="<?= esc($input['rate'] ?? '8.5') ?>" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Tenure (Months)</label>
                <input type="number" min="1" name="tenure" class="form-control" value="<?= esc($input['tenure'] ?? '12') ?>" required></div>
            <div class="form-group"><label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= esc($input['start_date'] ?? date('Y-m-d')) ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Interest Type</label>
                <select name="type" class="form-control">
                    <?php foreach (['simple' => 'Simple', 'compound' => 'Compound'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($input['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div class="form-group"><label>Day-count Convention</label>
                <select name="convention" class="form-control">
                    <?php foreach (['actual/365', 'actual/actual', '30/360'] as $c): ?>
                        <option value="<?= $c ?>" <?= ($input['convention'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select></div>
        </div>
        <div class="form-group"><label>Compounding per year (compound only)</label>
            <input type="number" min="1" max="12" name="times" class="form-control" value="<?= esc($input['times'] ?? '4') ?>"></div>
        <button type="submit" class="btn-primary">Calculate</button>
    </form>
</div>

<?php if ($result): ?>
    <div class="form-card" style="margin-top:24px;">
        <h3 style="margin-bottom:12px;">Result</h3>
        <div class="info-row"><span class="info-label">Maturity Date</span><span class="info-val"><?= esc($result['maturity_date']) ?></span></div>
        <div class="info-row"><span class="info-label">Days</span><span class="info-val"><?= (int) $result['days'] ?></span></div>
        <div class="info-row"><span class="info-label">Interest Earned</span><span class="info-val">Rs. <?= number_format($result['interest_earned'], 2) ?></span></div>
        <div class="info-row"><span class="info-label">Maturity Amount</span><span class="info-val" style="color:var(--primary);font-size:18px;">Rs. <?= number_format($result['maturity_amount'], 2) ?></span></div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>