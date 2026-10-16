<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Security &amp; Audit Trail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>@media print{nav,.filters,.no-print{display:none!important}body{background:#fff!important}.container{max-width:none!important}.table{font-size:10px}}</style>
</head>
<body class="bg-light">
<main class="container py-4">
    <nav class="mb-3"><a href="<?= base_url('dashboard') ?>">Dashboard</a> / Security &amp; Audit</nav>
    <div class="d-flex justify-content-between align-items-center mb-3"><div><h1 class="h3 mb-1">Security &amp; Audit Trail</h1><p class="text-secondary mb-0">Review authentication and banking activity.</p></div><button type="button" class="btn btn-outline-secondary no-print" onclick="window.print()">Print</button></div>
    <form method="get" class="row g-2 align-items-end mb-3 filters">
        <div class="col-sm-6 col-lg-2"><label class="form-label">Module</label><select class="form-select" name="module"><option value="">All modules</option><?php foreach($modules as $module): ?><option value="<?= esc($module) ?>" <?= ($filters['module'] ?? '')===$module?'selected':'' ?>><?= esc($module) ?></option><?php endforeach ?></select></div>
        <div class="col-sm-6 col-lg-2"><label class="form-label">Action</label><select class="form-select" name="action"><option value="">All actions</option><?php foreach($actions as $action): ?><option value="<?= esc($action) ?>" <?= ($filters['action'] ?? '')===$action?'selected':'' ?>><?= esc($action) ?></option><?php endforeach ?></select></div>
        <div class="col-sm-6 col-lg-2"><label class="form-label">Start date</label><input class="form-control" type="date" name="start_date" value="<?= esc($filters['start_date'] ?? '') ?>"></div>
        <div class="col-sm-6 col-lg-2"><label class="form-label">End date</label><input class="form-control" type="date" name="end_date" value="<?= esc($filters['end_date'] ?? '') ?>"></div>
        <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
        <div class="col-auto"><button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print Report</button></div>
    </form>
    <div class="table-responsive bg-white rounded shadow-sm"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Timestamp</th><th>User</th><th>Module</th><th>Action</th><th>Reference ID</th><th>Details</th><th>IP Address</th></tr></thead><tbody>
    <?php foreach($logs as $log): ?><tr><td class="text-nowrap"><?= esc($log['created_at'] ?? '') ?></td><td><?= esc($log['username'] ?? '—') ?><br><span class="badge text-bg-secondary"><?= esc($log['role'] ?? 'Unknown') ?></span></td><td><?= esc($log['module']) ?></td><td><span class="badge text-bg-primary"><?= esc($log['action']) ?></span></td><td><?= esc($log['reference_id'] ?? '—') ?></td><td class="small" style="max-width:320px;word-break:break-word;"><?= esc($log['details'] ?? '') ?></td><td><?= esc($log['ip_address'] ?? '—') ?></td></tr><?php endforeach ?>
    <?php if (!$logs): ?><tr><td colspan="7" class="text-center text-secondary py-4">No audit events match these filters.</td></tr><?php endif ?>
    </tbody></table></div><p class="small text-secondary mt-2">Showing up to 100 most recent matching events.</p>
</main>
</body></html>
