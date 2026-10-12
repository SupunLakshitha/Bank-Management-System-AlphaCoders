<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransfersTable extends Migration
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
            'transfer_no' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
                'unique'     => true,
            ],
            'from_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'to_account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'narration' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['POSTED', 'REVERSED'],
                'default'    => 'POSTED',
            ],
            'journal_entry_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('from_account_id', 'accounts', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('to_account_id', 'accounts', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('journal_entry_id', 'journal_entries', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('transfers');
    }

    public function down()
    {
        $this->forge->dropTable('transfers');
    }
}
