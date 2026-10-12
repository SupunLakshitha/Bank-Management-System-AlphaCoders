<?= view('partials/bank_open', [
    'title'    => 'BMS - Transaction Ledger',
    'active'   => 'ledger',
    'username' => $username ?? '',
    'role'     => $role ?? '',
]) ?>

<?php $balanced = abs($totalDebit - $totalCredit) < 0.01; ?>

<div class="page-header">
    <div>
        <h1>Transaction ledger</h1>
        <p>Every balance change is posted here first. Deposit balances are updated only from these entries.</p>
    </div>
    <?php if (! empty($canPost)): ?>
        <a class="btn-primary" href="<?= base_url('ledger/post') ?>">Post transaction</a>
    <?php endif; ?>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p>Transactions</p>
        <h3><?= count($entries) ?></h3>
    </div>
    <div class="stat-card">
        <p>Total debits</p>
        <h3>Rs. <?= number_format($totalDebit, 2) ?></h3>
    </div>
    <div class="stat-card">
        <p>Total credits</p>
        <h3>Rs. <?= number_format($totalCredit, 2) ?></h3>
    </div>
    <div class="stat-card">
        <p>Customer deposits</p>
        <h3>Rs. <?= number_format($depositTotal, 2) ?></h3>
        <span class="muted"><?= (int) $accountCount ?> active accounts</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Transaction register</h2>
        <form method="get" action="<?= base_url('ledger') ?>" style="display:flex; gap:8px; align-items:center;">
            <select name="type" onchange="this.form.submit()" style="padding:8px 10px; border:1px solid var(--border); border-radius:8px;">
                <option value="">All types</option>
                <?php foreach ($typeLabels as $code => $label): ?>
                    <option value="<?= esc($code) ?>" <?= $activeType === $code ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="<?= $balanced ? 'balance-ok' : 'balance-bad' ?>">
                <?= $balanced ? 'Books are in balance' : 'Books are out of balance' ?>
            </span>
        </form>
    </div>
    <?php if ($entries === []): ?>
        <div class="empty">No transactions have been posted yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>TX ID</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th class="num">Amount</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><strong><?= esc($entry['tx_id']) ?></strong></td>
                        <td>
                            <?php if ($entry['type'] === 'TRANSFER' && ! empty($entry['counter_account_no'])): ?>
                                <strong><?= esc($entry['account_no']) ?> → <?= esc($entry['counter_account_no']) ?></strong>
                                <div class="muted"><?= esc($entry['full_name'] ?? '') ?> → <?= esc($entry['counter_name'] ?? '') ?></div>
                            <?php elseif (! empty($entry['account_no'])): ?>
                                <strong><?= esc($entry['account_no']) ?></strong>
                                <div class="muted"><?= esc($entry['full_name'] ?? '') ?></div>
                            <?php else: ?>
                                <span class="muted">Multiple accounts</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-blue"><?= esc($typeLabels[$entry['type']] ?? $entry['type']) ?></span></td>
                        <td class="num"><strong>Rs. <?= number_format((float) $entry['amount'], 2) ?></strong></td>
                        <td><?= esc($entry['tx_date']) ?></td>
                        <td><a class="btn-ghost" href="<?= base_url('ledger/' . $entry['id']) ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?= view('partials/bank_close') ?>
