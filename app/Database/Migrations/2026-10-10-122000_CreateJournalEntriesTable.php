<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJournalEntriesTable extends Migration
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
            'entry_no' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
                'unique'     => true,
            ],
            'entry_date' => [
                'type' => 'DATETIME',
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
            ],
            'reference_no' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
                'null'       => true,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'branch_id' => [
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
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['POSTED', 'REVERSED'],
                'default'    => 'POSTED',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('reference_type');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('journal_entries');
    }

    public function down()
    {
        $this->forge->dropTable('journal_entries');
    }
}
