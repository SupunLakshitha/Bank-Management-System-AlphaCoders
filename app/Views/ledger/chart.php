<?= view('partials/bank_open', [
    'title'    => 'BMS - Chart of Accounts',
    'active'   => 'chart',
    'username' => $username ?? '',
    'role'     => $role ?? '',
]) ?>

<?php
$balanced = abs($totalDebit - $totalCredit) < 0.01;
$typeClass = [
    'ASSET'     => 'badge-blue',
    'LIABILITY' => 'badge-purple',
    'EQUITY'    => 'badge-slate',
    'INCOME'    => 'badge-green',
    'EXPENSE'   => 'badge-amber',
];
?>

<div class="page-header">
    <div>
        <h1>Chart of accounts</h1>
        <p>Balances follow the normal side of each account. Assets and expenses are debit-normal. Liabilities, equity, and income are credit-normal.</p>
    </div>
    <a class="btn-ghost" href="<?= base_url('ledger') ?>">Back to ledger</a>
</div>

<div class="card">
    <div class="card-header">
        <h2>General ledger accounts</h2>
        <span class="<?= $balanced ? 'balance-ok' : 'balance-bad' ?>">
            Trial balance <?= $balanced ? 'agrees' : 'does not agree' ?> · Dr <?= number_format($totalDebit, 2) ?> / Cr <?= number_format($totalCredit, 2) ?>
        </span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Account</th>
                <th>Type</th>
                <th>Normal</th>
                <th class="num">Debit</th>
                <th class="num">Credit</th>
                <th class="num">Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td><strong><?= esc($account['code']) ?></strong></td>
                    <td><?= esc($account['name']) ?></td>
                    <td><span class="badge <?= esc($typeClass[$account['account_type']] ?? 'badge-slate') ?>"><?= esc($account['account_type']) ?></span></td>
                    <td><?= esc($account['normal_balance']) ?></td>
                    <td class="num"><?= number_format((float) $account['total_debit'], 2) ?></td>
                    <td class="num"><?= number_format((float) $account['total_credit'], 2) ?></td>
                    <td class="num"><strong>Rs. <?= number_format((float) $account['balance'], 2) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= view('partials/bank_close') ?>
