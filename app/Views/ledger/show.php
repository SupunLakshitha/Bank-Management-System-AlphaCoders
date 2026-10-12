<?= view('partials/bank_open', [
    'title'    => 'BMS - ' . ($entry['tx_id'] ?? 'Transaction'),
    'active'   => 'ledger',
    'username' => $username ?? '',
    'role'     => $role ?? '',
]) ?>

<?php
$debitTotal = 0.0;
$creditTotal = 0.0;
foreach ($lines as $line) {
    $debitTotal += (float) $line['debit'];
    $creditTotal += (float) $line['credit'];
}
$label = $typeLabels[$entry['type']] ?? $entry['type'];
?>

<div class="page-header">
    <div>
        <h1><?= esc($entry['tx_id']) ?></h1>
        <p><?= esc($entry['description']) ?></p>
    </div>
    <a class="btn-ghost" href="<?= base_url('ledger') ?>">Back to ledger</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header"><h2>Transaction</h2></div>
    <div class="pad" style="display:grid; grid-template-columns: 160px 1fr; gap: 10px 18px; font-size: 15px;">
        <span class="muted">TX ID</span>
        <strong><?= esc($entry['tx_id']) ?></strong>
        <span class="muted">Account</span>
        <strong>
            <?php if ($entry['type'] === 'TRANSFER' && ! empty($entry['counter_account_no'])): ?>
                <?= esc($entry['account_no']) ?> → Rs. <?= number_format((float) $entry['amount'], 2) ?> → <?= esc($entry['counter_account_no']) ?>
                <div class="muted"><?= esc($entry['full_name'] ?? '') ?> → <?= esc($entry['counter_name'] ?? '') ?></div>
            <?php else: ?>
                <?= ! empty($entry['account_no']) ? esc($entry['account_no']) : 'Multiple accounts' ?>
                <?php if (! empty($entry['full_name'])): ?>
                    <span class="muted"> · <?= esc($entry['full_name']) ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </strong>
        <span class="muted">Type</span>
        <strong><?= esc($label) ?></strong>
        <span class="muted">Amount</span>
        <strong>Rs. <?= number_format((float) $entry['amount'], 2) ?></strong>
        <span class="muted">Date</span>
        <strong><?= esc($entry['tx_date']) ?></strong>
        <span class="muted">Posted by</span>
        <span><?= esc($entry['created_by_name'] ?? 'System') ?><?= ! empty($entry['branch_name']) ? ' · ' . esc($entry['branch_name']) : '' ?></span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Ledger entries</h2>
        <span class="<?= abs($debitTotal - $creditTotal) < 0.01 ? 'balance-ok' : 'balance-bad' ?>">
            <?= abs($debitTotal - $creditTotal) < 0.01 ? 'Entry is balanced' : 'Entry is out of balance' ?>
        </span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Ledger account</th>
                <th>Customer account</th>
                <th>Narration</th>
                <th class="num">Debit</th>
                <th class="num">Credit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lines as $line): ?>
                <tr>
                    <td>
                        <strong><?= esc($line['gl_code']) ?></strong>
                        <div class="muted"><?= esc($line['gl_name']) ?></div>
                    </td>
                    <td>
                        <?php if (! empty($line['account_no'])): ?>
                            <?= esc($line['account_no']) ?>
                            <div class="muted"><?= esc($line['full_name'] ?? '') ?></div>
                        <?php else: ?>
                            <span class="muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($line['narration'] ?? '') ?></td>
                    <td class="num"><?= (float) $line['debit'] > 0 ? 'Rs. ' . number_format((float) $line['debit'], 2) : '—' ?></td>
                    <td class="num"><?= (float) $line['credit'] > 0 ? 'Rs. ' . number_format((float) $line['credit'], 2) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td class="num"><strong>Rs. <?= number_format($debitTotal, 2) ?></strong></td>
                <td class="num"><strong>Rs. <?= number_format($creditTotal, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
</div>

<?= view('partials/bank_close') ?>
