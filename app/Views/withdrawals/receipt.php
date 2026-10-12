<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Receipt - <?= esc($withdrawal['withdrawal_id']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }
        body { background: #f1f5f9; color: #0f172a; padding: 40px 20px; }
        .receipt-container { max-width: 600px; margin: 0 auto; background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); padding: 36px; }
        .receipt-header { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 20px; margin-bottom: 24px; }
        .bank-badge { display: inline-block; background: #4361ee; color: white; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; text-transform: uppercase; margin-bottom: 6px; }
        .receipt-header h1 { font-size: 20px; font-weight: 800; color: #1e1e2f; }
        .receipt-header p { font-size: 13px; color: #64748b; margin-top: 4px; }
        .receipt-id { font-size: 16px; font-weight: 800; color: #ef4444; margin-top: 10px; }
        .row-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .row-item.highlight { background: #fef2f2; padding: 12px 14px; border-radius: 8px; border: 1px solid #fecaca; margin: 16px 0; }
        .label { color: #64748b; font-weight: 500; }
        .value { font-weight: 700; color: #0f172a; text-align: right; }
        .actions-bar { margin-top: 30px; display: flex; justify-content: space-between; align-items: center; }
        .btn-print { background: #ef4444; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; }
        .btn-back { color: #4361ee; text-decoration: none; font-size: 13px; font-weight: 600; }
        @media print {
            body { background: white; padding: 0; }
            .receipt-container { box-shadow: none; border: none; padding: 20px; }
            .actions-bar { display: none; }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <div class="receipt-header">
            <div class="bank-badge">EGOTECH BANK</div>
            <h1>CUSTOMER CASH WITHDRAWAL VOUCHER</h1>
            <p><?= esc($withdrawal['branch_name'] ?? 'Main Branch') ?> &bull; <?= date('M d, Y h:i A', strtotime($withdrawal['created_at'])) ?></p>
            <div class="receipt-id"><?= esc($withdrawal['withdrawal_id']) ?></div>
        </div>

        <div class="row-item">
            <span class="label">Debited Account:</span>
            <span class="value" style="color: #4361ee; font-size: 15px;"><?= esc($withdrawal['account_no']) ?></span>
        </div>
        <div class="row-item">
            <span class="label">Account Holder:</span>
            <span class="value"><?= esc($withdrawal['customer_name']) ?> (<?= esc($withdrawal['cust_code']) ?>)</span>
        </div>
        <div class="row-item">
            <span class="label">NIC / Passport:</span>
            <span class="value"><?= esc($withdrawal['customer_nic'] ?? '—') ?></span>
        </div>
        <div class="row-item">
            <span class="label">Withdrawal Method:</span>
            <span class="value"><?= esc($withdrawal['withdrawal_method']) ?></span>
        </div>
        <?php if(!empty($withdrawal['reference_number'])): ?>
            <div class="row-item">
                <span class="label">Reference / Slip No:</span>
                <span class="value"><?= esc($withdrawal['reference_number']) ?></span>
            </div>
        <?php endif; ?>

        <div class="row-item highlight">
            <span class="label" style="font-weight: 700; color: #991b1b; font-size: 15px;">Amount Paid Out:</span>
            <span class="value" style="color: #dc2626; font-size: 18px; font-weight: 800;">- Rs. <?= number_format((float)$withdrawal['amount'], 2) ?></span>
        </div>

        <div class="row-item">
            <span class="label">Previous Balance:</span>
            <span class="value">Rs. <?= number_format((float)$withdrawal['previous_balance'], 2) ?></span>
        </div>
        <div class="row-item">
            <span class="label">Remaining Available Balance:</span>
            <span class="value" style="font-size: 15px; color: #1e1e2f;">Rs. <?= number_format((float)$withdrawal['new_balance'], 2) ?></span>
        </div>
        <div class="row-item">
            <span class="label">Disbursing Teller:</span>
            <span class="value"><?= esc($withdrawal['teller_username'] ?? 'System') ?></span>
        </div>
        <?php if(!empty($withdrawal['remarks'])): ?>
            <div class="row-item">
                <span class="label">Remarks / Verification:</span>
                <span class="value"><?= esc($withdrawal['remarks']) ?></span>
            </div>
        <?php endif; ?>

        <div style="margin-top: 40px; border-top: 1px solid #cbd5e1; padding-top: 12px; display: flex; justify-content: space-between; font-size: 11px; color: #64748b;">
            <span>Teller Signature: __________________</span>
            <span>Customer Signature: __________________</span>
        </div>

        <div class="actions-bar">
            <a href="<?= base_url('withdrawals') ?>" class="btn-back">&larr; Back to Withdrawals</a>
            <button onclick="window.print()" class="btn-print">🖨 Print Official Voucher</button>
        </div>
    </div>

</body>
</html>
