<?php
$db = new mysqli('localhost', 'root', '', 'bms');

function dump($db, $title, $sql) {
    echo "===== $title =====\n";
    $r = $db->query($sql);
    if (!$r) { echo $db->error . "\n\n"; return; }
    while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";
    echo "\n";
}

dump($db, 'branches', 'SELECT id, name, code FROM branches ORDER BY id');
dump($db, 'staff', "SELECT u.username, r.name role, b.name branch FROM users u JOIN roles r ON r.id=u.role_id JOIN branches b ON b.id=u.branch_id WHERE r.name <> 'Customer' ORDER BY b.name, u.username");
dump($db, 'customers', 'SELECT c.customer_id, c.full_name, b.name branch FROM customers c JOIN branches b ON b.id=c.branch_id ORDER BY b.name');
dump($db, 'accounts', 'SELECT a.account_no, a.account_type, a.balance, b.name branch, c.customer_id FROM accounts a JOIN branches b ON b.id=a.branch_id JOIN customers c ON c.id=a.customer_id ORDER BY b.name');
dump($db, 'tx by branch', 'SELECT b.name branch, COUNT(*) txs FROM transactions t JOIN branches b ON b.id=t.branch_id GROUP BY b.name');
dump($db, 'nulls', "SELECT
 (SELECT COUNT(*) FROM users WHERE branch_id IS NULL) users_null,
 (SELECT COUNT(*) FROM customers WHERE branch_id IS NULL) customers_null,
 (SELECT COUNT(*) FROM accounts WHERE branch_id IS NULL) accounts_null,
 (SELECT COUNT(*) FROM transactions WHERE branch_id IS NULL) tx_null,
 (SELECT COUNT(*) FROM loans WHERE branch_id IS NULL) loans_null");
