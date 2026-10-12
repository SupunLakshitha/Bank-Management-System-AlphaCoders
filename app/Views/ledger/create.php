<?= view('partials/bank_open', [
    'title'    => 'BMS - Post Transaction',
    'active'   => 'ledger',
    'username' => $username ?? '',
    'role'     => $role ?? '',
]) ?>

<div class="page-header">
    <div>
        <h1>Post transaction</h1>
        <p>Deposit, withdrawal, loan repayment, interest, fees, and adjustments all go through the ledger. The account balance changes only after the entries balance.</p>
    </div>
    <a class="btn-ghost" href="<?= base_url('ledger') ?>">Back to ledger</a>
</div>

<div class="card">
    <div class="card-header"><h2>New transaction</h2></div>
    <form class="pad" action="<?= base_url('ledger/post') ?>" method="post" style="max-width: 760px;">
        <div class="form-grid">
            <div class="field">
                <label for="type">Type</label>
                <select id="type" name="type" required>
                    <?php foreach ($typeLabels as $code => $label): ?>
                        <option value="<?= esc($code) ?>" <?= old('type') === $code ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="tx_date">Date</label>
                <input id="tx_date" name="tx_date" type="date" required value="<?= esc(old('tx_date') ?: date('Y-m-d')) ?>">
            </div>
            <div class="field" id="loan-field" style="display:none;">
                <label for="loan_id">Loan</label>
                <select id="loan_id" name="loan_id">
                    <option value="">Select loan</option>
                    <?php foreach ($loans as $loan): ?>
                        <option value="<?= (int) $loan['id'] ?>" data-customer="<?= (int) $loan['customer_id'] ?>" <?= (string) old('loan_id') === (string) $loan['id'] ? 'selected' : '' ?>>
                            <?= esc($loan['loan_account_no']) ?> · <?= esc($loan['full_name']) ?> · outstanding Rs. <?= number_format((float) $loan['outstanding_balance'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" id="side-field" style="display:none;">
                <label for="adjustment_side">Adjustment</label>
                <select id="adjustment_side" name="adjustment_side">
                    <option value="credit" <?= old('adjustment_side') === 'debit' ? '' : 'selected' ?>>Credit account (increase balance)</option>
                    <option value="debit" <?= old('adjustment_side') === 'debit' ? 'selected' : '' ?>>Debit account (decrease balance)</option>
                </select>
            </div>
            <div class="field full">
                <label for="account_id">Account</label>
                <select id="account_id" name="account_id" required>
                    <option value="">Select account</option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= (int) $account['id'] ?>" data-customer="<?= (int) $account['customer_id'] ?>" data-balance="<?= esc($account['balance']) ?>" <?= (string) old('account_id') === (string) $account['id'] ? 'selected' : '' ?>>
                            <?= esc($account['account_no']) ?> · <?= esc($account['full_name']) ?> · <?= esc($account['account_type']) ?> · Rs. <?= number_format((float) $account['balance'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="muted" id="available"></span>
            </div>
            <div class="field">
                <label for="amount">Amount (LKR)</label>
                <input id="amount" name="amount" type="number" min="0.01" step="0.01" required value="<?= esc(old('amount') ?? '') ?>" placeholder="50000.00">
            </div>
            <div class="field">
                <label for="narration">Narration</label>
                <input id="narration" name="narration" type="text" maxlength="255" value="<?= esc(old('narration') ?? '') ?>" placeholder="Optional note">
            </div>
            <div class="field full">
                <button class="btn-primary" type="submit">Post to ledger</button>
            </div>
        </div>
    </form>
</div>

<script>
    const typeSelect = document.getElementById('type');
    const loanField = document.getElementById('loan-field');
    const sideField = document.getElementById('side-field');
    const loanSelect = document.getElementById('loan_id');
    const accountSelect = document.getElementById('account_id');
    const available = document.getElementById('available');

    function refreshForm() {
        const type = typeSelect.value;
        loanField.style.display = type === 'LOAN_REPAYMENT' ? 'flex' : 'none';
        sideField.style.display = type === 'ADJUSTMENT' ? 'flex' : 'none';
        const customerId = type === 'LOAN_REPAYMENT' && loanSelect.selectedOptions[0]
            ? loanSelect.selectedOptions[0].getAttribute('data-customer')
            : '';
        Array.from(accountSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = customerId !== '' && option.getAttribute('data-customer') !== customerId;
        });
        const selected = accountSelect.selectedOptions[0];
        const balance = selected ? selected.getAttribute('data-balance') : null;
        available.textContent = balance ? 'Available Rs. ' + Number(balance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '';
    }

    typeSelect.addEventListener('change', refreshForm);
    loanSelect.addEventListener('change', refreshForm);
    accountSelect.addEventListener('change', refreshForm);
    refreshForm();
</script>

<?= view('partials/bank_close') ?>
