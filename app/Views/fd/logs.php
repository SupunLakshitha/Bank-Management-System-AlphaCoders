<?= $this->extend('fd/layout') ?>

<?= $this->section('content') ?>
<?php $sum = array_sum(array_column($logs, 'interest_amount')); ?>
<div class="page-header">
    <div><h1>FD Interest Accrual Logs</h1><p>Audit trail written by the <code>php spark fd:accrue</code> job.</p></div>
</div>

<div class="stats-grid">
    <div class="stat-card"><h3><?= count($logs) ?></h3><p>Log entries (latest 200)</p></div>
    <div class="stat-card"><h3>Rs. <?= number_format($sum, 2) ?></h3><p>Interest accrued in these entries</p></div>
</div>

<div class="table-card">
    <div class="card-header">Accrual Entries</div>
    <?php if (empty($logs)): ?>
        <div class="empty-state"><h4>No accruals yet</h4><p>Run <code>php spark fd:accrue</code> to generate entries.</p></div>
    <?php else: ?>
        <table>
            <thead><tr><th>Date</th><th>FD Number</th><th>Principal</th><th>Interest Added</th><th>Total Accrued</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= esc($l['accrual_date']) ?></td>
                    <td><strong><?= esc($l['fd_number']) ?></strong></td>
                    <td>Rs. <?= number_format($l['principal'], 2) ?></td>
                    <td>+ Rs. <?= number_format($l['interest_amount'], 2) ?></td>
                    <td>Rs. <?= number_format($l['total_accrued'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>