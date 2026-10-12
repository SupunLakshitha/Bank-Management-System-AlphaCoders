<?= view('partials/bank_open', [
    'title'    => 'BMS - Transfers',
    'active'   => 'transfers',
    'username' => $username ?? '',
    'role'     => $role ?? '',
]) ?>

<div class="page-header">
    <div>
        <h1>Account transfers</h1>
        <p>One transfer posts both sides together. Account A is debited and Account B is credited for the same amount, or neither balance changes.</p>
    </div>
</div>

<div class="split">
    <div class="card">
        <div class="card-header"><h2>New transfer</h2></div>
        <form class="pad" action="<?= base_url('transfers') ?>" method="post">
            <?php if (count($accounts) < 2): ?>
                <div class="alert alert-error">At least two active deposit accounts are needed before a transfer can be posted.</div>
            <?php endif; ?>
            <div class="form-grid">
                <div class="field">
                    <label for="from_account_id">From account</label>
                    <select id="from_account_id" name="from_account_id" required>
                        <option value="">Select sender</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= (int) $account['id'] ?>" data-balance="<?= esc($account['balance']) ?>" <?= (string) old('from_account_id') === (string) $account['id'] ? 'selected' : '' ?>>
                                <?= esc($account['account_no']) ?> · <?= esc($account['full_name']) ?> · Rs. <?= number_format((float) $account['balance'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="to_account_id">To account</label>
                    <select id="to_account_id" name="to_account_id" required>
                        <option value="">Select receiver</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= (int) $account['id'] ?>" <?= (string) old('to_account_id') === (string) $account['id'] ? 'selected' : '' ?>>
                                <?= esc($account['account_no']) ?> · <?= esc($account['full_name']) ?> · <?= esc($account['account_type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="amount">Amount (LKR)</label>
                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" required value="<?= esc(old('amount') ?? '') ?>" placeholder="0.00">
                    <span class="muted" id="available"></span>
                </div>
                <div class="field">
                    <label for="narration">Narration</label>
                    <input id="narration" name="narration" type="text" maxlength="255" value="<?= esc(old('narration') ?? '') ?>" placeholder="Optional note">
                </div>
                <div class="field full">
                    <p id="movement" style="font-weight: 700; margin-bottom: 12px;"></p>
                    <button class="btn-primary" type="submit" <?= count($accounts) < 2 ? 'disabled' : '' ?>>Post transfer</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><h2>Deposit accounts</h2></div>
        <?php if ($accounts === []): ?>
            <div class="empty">No deposit accounts yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Type</th>
                        <th class="num">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td>
                                <strong><?= esc($account['account_no']) ?></strong>
                                <div class="muted"><?= esc($account['full_name']) ?> · <?= esc($account['customer_code']) ?></div>
                            </td>
                            <td><span class="badge <?= $account['account_type'] === 'CURRENT' ? 'badge-purple' : 'badge-green' ?>"><?= esc($account['account_type']) ?></span></td>
                            <td class="num"><strong>Rs. <?= number_format((float) $account['balance'], 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Recent transfers</h2></div>
    <?php if ($transfers === []): ?>
        <div class="empty">No transfers posted yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Transfer</th>
                    <th>Movement</th>
                    <th>Transaction</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transfers as $transfer): ?>
                    <tr>
                        <td>
                            <strong><?= esc($transfer['transfer_no']) ?></strong>
                            <div class="muted"><?= esc(date('d M Y, H:i', strtotime($transfer['created_at']))) ?></div>
                        </td>
                        <td>
                            <strong><?= esc($transfer['from_no']) ?> → Rs. <?= number_format((float) $transfer['amount'], 2) ?> → <?= esc($transfer['to_no']) ?></strong>
                            <div class="muted"><?= esc($transfer['from_name']) ?> → <?= esc($transfer['to_name']) ?></div>
                        </td>
                        <td>
                            <?php if (! empty($transfer['transaction_pk'])): ?>
                                <a class="btn-ghost" href="<?= base_url('ledger/' . $transfer['transaction_pk']) ?>"><?= esc($transfer['tx_id']) ?></a>
                            <?php endif; ?>
                            <?php if (! empty($transfer['narration'])): ?>
                                <div class="muted"><?= esc($transfer['narration']) ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
    const fromSelect = document.getElementById('from_account_id');
    const toSelect = document.getElementById('to_account_id');
    const amountInput = document.getElementById('amount');
    const available = document.getElementById('available');
    const movement = document.getElementById('movement');
    function label(select) {
        const option = select.options[select.selectedIndex];
        if (!option || !option.value) return '';
        return option.text.split('·')[0].trim();
    }
    function showBalance() {
        const option = fromSelect.options[fromSelect.selectedIndex];
        const balance = option ? option.getAttribute('data-balance') : null;
        available.textContent = balance ? 'Available Rs. ' + Number(balance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
        const from = label(fromSelect);
        const to = label(toSelect);
        const amount = Number(amountInput.value);
        movement.textContent = from && to && amount > 0
            ? from + ' → Rs. ' + amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' → ' + to
            : '';
    }
    fromSelect.addEventListener('change', showBalance);
    toSelect.addEventListener('change', showBalance);
    amountInput.addEventListener('input', showBalance);
    showBalance();
</script>

<?= view('partials/bank_close') ?>
