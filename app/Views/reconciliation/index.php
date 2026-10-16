<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Financial Reconciliation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>@media print{nav,.filters,.no-print{display:none!important}body{background:#fff!important}.container{max-width:none!important}.card{box-shadow:none!important}.table{font-size:10px}}</style>
</head>
<body class="bg-light">
<main class="container py-4">
    <nav class="mb-3"><a href="<?= base_url('dashboard') ?>">Dashboard</a> / Financial Reconciliation</nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4"><div><h1 class="h3 mb-1">Account &amp; Ledger Reconciliation</h1><p class="text-secondary mb-0">Compare stored account balances with recorded deposits, withdrawals, and transfers. No opening balance is tracked in the account schema.</p></div><div class="no-print"><button class="btn btn-outline-secondary" onclick="window.print()" type="button">Print</button> <button class="btn btn-primary" onclick="exportReconciliationCsv()" type="button">Export CSV</button></div></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Total Accounts Audited</div><div class="fs-3 fw-semibold"><?= number_format($totalAccounts) ?></div></div></div></div>
        <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Balanced Accounts</div><div class="fs-3 fw-semibold text-success"><?= number_format($balancedCount) ?></div></div></div></div>
        <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">Discrepancies Found</div><div class="fs-3 fw-semibold text-danger"><?= number_format($discrepancyCount) ?></div></div></div></div>
        <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="small text-secondary">General Ledger State</div><div><span class="badge text-bg-<?= $ledger['status']==='BALANCED'?'success':'danger' ?> fs-6"><?= esc($ledger['status']) ?></span></div><div class="small text-secondary mt-1">Variance: Rs. <?= number_format((float)$ledger['variance'],2) ?></div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body d-flex flex-wrap gap-4"><div><span class="small text-secondary">Ledger Debits</span><div class="fw-semibold">Rs. <?= number_format((float)$ledger['total_debits'],2) ?></div></div><div><span class="small text-secondary">Ledger Credits</span><div class="fw-semibold">Rs. <?= number_format((float)$ledger['total_credits'],2) ?></div></div><div><span class="small text-secondary">Balance Check</span><div class="fw-semibold"><?= esc($ledger['status']) ?></div></div></div></div>

    <form method="get" class="d-flex flex-wrap align-items-end gap-2 mb-3 filters"><div><label for="status" class="form-label">Filter by Status</label><select id="status" name="status" class="form-select"><option value="all" <?= $statusFilter==='all'?'selected':'' ?>>All</option><option value="discrepancy" <?= $statusFilter==='discrepancy'?'selected':'' ?>>Discrepancy Only</option></select></div><button class="btn btn-primary">Filter</button></form>
    <div class="table-responsive bg-white rounded shadow-sm"><table id="reconciliation-table" class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Account No</th><th>Account Holder</th><th>Current Stored Balance (LKR)</th><th>Computed Ledger Balance (LKR)</th><th>Variance (LKR)</th><th>Integrity Status</th></tr></thead><tbody>
    <?php foreach ($accounts as $account): ?><tr><td><?= esc($account['account_no']) ?></td><td><?= esc($account['account_holder']) ?></td><td>Rs. <?= number_format((float)$account['stored_balance'],2) ?></td><td>Rs. <?= number_format((float)$account['computed_balance'],2) ?></td><td class="<?= $account['variance']==0.0?'':'text-danger fw-semibold' ?>">Rs. <?= number_format((float)$account['variance'],2) ?></td><td><span class="badge text-bg-<?= $account['reconciliation_status']==='BALANCED'?'success':'danger' ?>"><?= esc($account['reconciliation_status']) ?></span></td></tr><?php endforeach ?>
    <?php if (!$accounts): ?><tr><td colspan="6" class="text-center text-secondary py-4">No accounts match this filter.</td></tr><?php endif ?>
    </tbody></table></div>
</main>
<script>
function exportReconciliationCsv() {
    const rows = Array.from(document.querySelectorAll('#reconciliation-table tr'));
    const csv = rows.map(row => Array.from(row.querySelectorAll('th,td')).map(cell => '"' + cell.innerText.replaceAll('"', '""').trim() + '"').join(',')).join('\n');
    const link = document.createElement('a');
    link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
    link.download = 'account-reconciliation.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>
</body>
</html>
