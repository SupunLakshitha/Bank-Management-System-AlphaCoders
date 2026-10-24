<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnforceBranchAssociations extends Migration
{
    public function up()
    {
        $colomboId = $this->ensureBranches();

        $hasBranch = $this->db->query("SHOW COLUMNS FROM `customers` LIKE 'branch_id'")->getNumRows() > 0;
        if (! $hasBranch) {
            $this->forge->addColumn('customers', [
                'branch_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'user_id',
                ],
            ]);
        }

        $this->backfillNullBranch('customers', $colomboId);
        $this->requireBranchColumn('customers');

        $this->db->query(
            'UPDATE loans l
             INNER JOIN customers c ON c.id = l.customer_id
             SET l.branch_id = c.branch_id
             WHERE l.branch_id IS NULL'
        );
        $this->db->query(
            'UPDATE loan_applications a
             INNER JOIN customers c ON c.id = a.customer_id
             SET a.branch_id = c.branch_id
             WHERE a.branch_id IS NULL'
        );

        $this->backfillNullBranch('users', $colomboId);
        $this->backfillNullBranch('loans', $colomboId);
        $this->backfillNullBranch('loan_applications', $colomboId);

        $this->requireBranchColumn('users');
        $this->requireBranchColumn('loans');
        $this->requireBranchColumn('loan_applications');

        $fdHasBranch = $this->db->tableExists('fixed_deposits')
            && $this->db->query("SHOW COLUMNS FROM `fixed_deposits` LIKE 'branch_id'")->getNumRows() > 0;

        if ($this->db->tableExists('fixed_deposits') && ! $fdHasBranch) {
            $this->forge->addColumn('fixed_deposits', [
                'branch_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'customer_id',
                ],
            ]);
            $this->db->query(
                'UPDATE fixed_deposits f
                 INNER JOIN customers c ON c.id = f.customer_id
                 SET f.branch_id = c.branch_id
                 WHERE f.branch_id IS NULL'
            );
            $this->backfillNullBranch('fixed_deposits', $colomboId);
            $this->requireBranchColumn('fixed_deposits');
        }

        if ($this->db->tableExists('accounts') && $this->db->fieldExists('branch_id', 'accounts')) {
            $this->db->query(
                'UPDATE accounts a
                 INNER JOIN customers c ON c.id = a.customer_id
                 SET a.branch_id = c.branch_id
                 WHERE a.branch_id IS NULL'
            );
            $this->backfillNullBranch('accounts', $colomboId);
            $this->requireBranchColumn('accounts');
        }

        if ($this->db->tableExists('transactions') && $this->db->fieldExists('branch_id', 'transactions')) {
            if ($this->db->fieldExists('account_id', 'transactions')) {
                $this->db->query(
                    'UPDATE transactions t
                     INNER JOIN accounts a ON a.id = t.account_id
                     SET t.branch_id = a.branch_id
                     WHERE t.branch_id IS NULL AND a.branch_id IS NOT NULL'
                );
            }
            $this->backfillNullBranch('transactions', $colomboId);
            $this->requireBranchColumn('transactions');
        }
    }

    public function down()
    {
        foreach (['transactions', 'accounts', 'loan_applications', 'loans', 'users'] as $table) {
            $this->relaxBranchColumn($table);
        }

        if ($this->db->tableExists('fixed_deposits') && $this->db->fieldExists('branch_id', 'fixed_deposits')) {
            $fk = $this->foreignKeyName('fixed_deposits', 'branch_id');
            if ($fk) {
                $this->db->query('ALTER TABLE `fixed_deposits` DROP FOREIGN KEY `' . $fk . '`');
            }
            $this->forge->dropColumn('fixed_deposits', 'branch_id');
        }

        if ($this->db->fieldExists('branch_id', 'customers')) {
            $fk = $this->foreignKeyName('customers', 'branch_id');
            if ($fk) {
                $this->db->query('ALTER TABLE `customers` DROP FOREIGN KEY `' . $fk . '`');
            }
            $this->forge->dropColumn('customers', 'branch_id');
        }
    }

    private function ensureBranches(): int
    {
        $branches = [
            ['name' => 'Colombo Branch', 'code' => 'CMB', 'location' => 'No. 100, Galle Road, Colombo 03'],
            ['name' => 'Kandy Branch', 'code' => 'KAN', 'location' => 'No. 25, Dalada Veediya, Kandy'],
            ['name' => 'Kegalle Branch', 'code' => 'KEG', 'location' => 'No. 12, Main Street, Kegalle'],
            ['name' => 'Galle Branch', 'code' => 'GAL', 'location' => 'No. 48, Church Street, Galle Fort'],
        ];

        foreach ($branches as $branch) {
            $existing = $this->db->table('branches')->where('code', $branch['code'])->get()->getRowArray();
            if (! $existing) {
                $this->db->table('branches')->insert($branch);
            }
        }

        $colombo = $this->db->table('branches')->where('code', 'CMB')->get()->getRowArray();
        if (! $colombo) {
            $colombo = $this->db->table('branches')->where('code', 'COL')->get()->getRowArray();
        }

        return (int) $colombo['id'];
    }

    private function backfillNullBranch(string $table, int $branchId): void
    {
        $this->db->table($table)->where('branch_id', null)->update(['branch_id' => $branchId]);
    }

    private function requireBranchColumn(string $table): void
    {
        $fk = $this->foreignKeyName($table, 'branch_id');
        if ($fk) {
            $this->db->query('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $fk . '`');
        }

        $this->db->query('ALTER TABLE `' . $table . '` MODIFY `branch_id` INT(11) UNSIGNED NOT NULL');
        $this->db->query(
            'ALTER TABLE `' . $table . '` ADD CONSTRAINT `' . $table . '_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT'
        );
    }

    private function relaxBranchColumn(string $table): void
    {
        if (! $this->db->fieldExists('branch_id', $table)) {
            return;
        }

        $fk = $this->foreignKeyName($table, 'branch_id');
        if ($fk) {
            $this->db->query('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $fk . '`');
        }

        $this->db->query('ALTER TABLE `' . $table . '` MODIFY `branch_id` INT(11) UNSIGNED NULL');
        $this->db->query(
            'ALTER TABLE `' . $table . '` ADD CONSTRAINT `' . $table . '_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT'
        );
    }

    private function foreignKeyName(string $table, string $column): ?string
    {
        $row = $this->db->query(
            'SELECT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$table, $column]
        )->getRowArray();

        return $row['name'] ?? null;
    }
}
