<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGlAccountsTable extends Migration
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
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'unique'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'account_type' => [
                'type'       => 'ENUM',
                'constraint' => ['ASSET', 'LIABILITY', 'EQUITY', 'INCOME', 'EXPENSE'],
            ],
            'normal_balance' => [
                'type'       => 'ENUM',
                'constraint' => ['DEBIT', 'CREDIT'],
            ],
            'is_system' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('gl_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('gl_accounts');
    }
}
