<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionEntriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'transaction_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'gl_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'debit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'credit' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
            ],
            'narration' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('transaction_id');
        $this->forge->addKey('gl_account_id');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('gl_account_id', 'gl_accounts', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('account_id', 'accounts', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('transaction_entries');
    }

    public function down()
    {
        $this->forge->dropTable('transaction_entries');
    }
}
