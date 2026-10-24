<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAccountTypeToAccounts extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('account_type_id', 'accounts')) {
            $this->forge->addColumn('accounts', [
                'account_type_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'customer_id'],
            ]);
            $this->db->query('ALTER TABLE accounts ADD CONSTRAINT fk_accounts_account_type FOREIGN KEY (account_type_id) REFERENCES account_types(id) ON DELETE RESTRICT ON UPDATE RESTRICT');
        }

        if (! $this->db->fieldExists('interest_rate', 'accounts')) {
            $this->forge->addColumn('accounts', [
                'interest_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            ]);
        }

        // Deposits module creates accounts without a GL account, so allow NULL
        $this->forge->modifyColumn('accounts', [
            'gl_account_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
    }

    public function down()
    {
        $this->db->query('ALTER TABLE accounts DROP FOREIGN KEY fk_accounts_account_type');
        $this->forge->dropColumn('accounts', ['account_type_id', 'interest_rate']);
    }
}