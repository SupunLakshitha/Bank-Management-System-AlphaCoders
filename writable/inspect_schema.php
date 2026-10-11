<?php
$db = new mysqli('localhost', 'root', '', 'bms');
$tables = ['branches', 'users', 'customers', 'accounts', 'transactions', 'loans', 'loan_applications', 'gl_accounts', 'fixed_deposits', 'migrations'];
foreach ($tables as $table) {
    echo "===== $table =====\n";
    $exists = $db->query("SHOW TABLES LIKE '$table'");
    if (!$exists || $exists->num_rows === 0) {
        echo "MISSING\n\n";
        continue;
    }
    $cols = $db->query("SHOW COLUMNS FROM `$table`");
    while ($col = $cols->fetch_assoc()) {
        echo $col['Field'] . ' | ' . $col['Type'] . ' | null=' . $col['Null'] . ' | key=' . $col['Key'] . ' | default=' . $col['Default'] . "\n";
    }
    $count = $db->query("SELECT COUNT(*) AS c FROM `$table`")->fetch_assoc();
    echo "rows=" . $count['c'] . "\n\n";
}
echo "===== migrations =====\n";
$m = $db->query('SELECT id, version, class, namespace FROM migrations ORDER BY id');
if ($m) {
    while ($row = $m->fetch_assoc()) {
        echo $row['id'] . ' ' . $row['version'] . ' ' . $row['class'] . "\n";
    }
}
