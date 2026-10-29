<?= $this->extend('fd/layout') ?>

<?= $this->section('content') ?>
<?php
$total  = count($products);
$active = count(array_filter($products, fn($p) => $p['is_active']));
?>
<div class="page-header">
    <div>
        <h1>Fixed Deposit Products</h1>
        <p>Define FD schemes with interest rates, tenures and minimum amounts. Deactivate seasonal products.</p>
    </div>
    <button class="btn-primary" onclick="openModal()">+ New FD Product</button>
</div>

<div class="stats-grid">
    <div class="stat-card"><h3><?= $total ?></h3><p>Total FD Products</p></div>
    <div class="stat-card"><h3><?= $active ?></h3><p>Active Products</p></div>
    <div class="stat-card"><h3><?= $total - $active ?></h3><p>Inactive (Seasonal)</p></div>
</div>

<div class="table-card">
    <div class="card-header">Configured FD Products</div>
    <?php if (empty($products)): ?>
        <div class="empty-state"><h4>No FD products yet</h4><p>Click "New FD Product" to create the first one.</p></div>
    <?php else: ?>
        <table>
            <thead><tr><th>Name</th><th>Rate</th><th>Interest Type</th><th>Tenure</th><th>Min Amount</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><strong><?= esc($p['name']) ?></strong></td>
                    <td><strong><?= number_format($p['interest_rate'], 2) ?>%</strong> p.a.</td>
                    <td>
                        <?php if ($p['interest_type'] === 'compound'): ?>
                            <span class="badge-type badge-compound">Compound (<?= (int) $p['compounding_per_year'] ?>x/yr)</span>
                        <?php else: ?>
                            <span class="badge-type badge-simple">Simple</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $p['tenure_months'] ?> Months</td>
                    <td>Rs. <?= number_format($p['min_amount'], 2) ?></td>
                    <td><?= $p['is_active'] ? '<span class="badge-active">● Active</span>' : '<span class="badge-inactive">● Inactive</span>' ?></td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action" onclick="editProduct(<?= esc(json_encode($p), 'attr') ?>)">Edit</button>
                            <form method="POST" action="<?= base_url('fd/products/toggle/' . $p['id']) ?>">
                                <?= csrf_field() ?>
                                <button class="btn-action <?= $p['is_active'] ? 'btn-deactivate' : 'btn-activate' ?>" type="submit">
                                    <?= $p['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="productModal">
    <div class="modal-card">
        <div class="modal-header"><h3 id="modalTitle">New FD Product</h3><button class="modal-close" onclick="closeModal()">&times;</button></div>
        <form id="productForm" method="POST" action="<?= base_url('fd/products/save') ?>">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group"><label>Product Name *</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. 12-Month Saver" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Interest Rate (% p.a.) *</label>
                        <input type="number" step="0.01" min="0" max="100" name="interest_rate" id="interest_rate" class="form-control" required></div>
                    <div class="form-group"><label>Tenure (Months) *</label>
                        <input type="number" min="1" name="tenure_months" id="tenure_months" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Interest Type *</label>
                        <select name="interest_type" id="interest_type" class="form-control">
                            <option value="simple">Simple</option>
                            <option value="compound">Compound</option>
                        </select></div>
                    <div class="form-group"><label>Compounding per year</label>
                        <input type="number" min="1" max="12" name="compounding_per_year" id="compounding_per_year" class="form-control" value="4">
                        <div class="form-hint">Compound only (4 = quarterly)</div></div>
                </div>
                <div class="form-group"><label>Minimum Amount (LKR) *</label>
                    <input type="number" step="any" min="1" name="min_amount" id="min_amount" class="form-control" required></div>
                <div class="form-group" style="display:flex;gap:8px;align-items:center;">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                    <label for="is_active" style="margin:0;">Active (available for new FDs)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-light" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="submitBtn">Save Product</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const modal = document.getElementById('productModal');
    const form  = document.getElementById('productForm');
    function openModal() {
        document.getElementById('modalTitle').innerText = 'New FD Product';
        document.getElementById('submitBtn').innerText = 'Save Product';
        form.action = "<?= base_url('fd/products/save') ?>";
        form.reset();
        document.getElementById('is_active').checked = true;
        modal.style.display = 'flex';
    }
    function closeModal() { modal.style.display = 'none'; }
    function editProduct(p) {
        document.getElementById('modalTitle').innerText = 'Edit: ' + p.name;
        document.getElementById('submitBtn').innerText = 'Update Product';
        form.action = "<?= base_url('fd/products/save') ?>/" + p.id;
        ['name','interest_rate','tenure_months','interest_type','compounding_per_year','min_amount'].forEach(k => document.getElementById(k).value = p[k]);
        document.getElementById('is_active').checked = (p.is_active == 1);
        modal.style.display = 'flex';
    }
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
</script>
<?= $this->endSection() ?>