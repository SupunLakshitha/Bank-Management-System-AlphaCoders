<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepositsTable extends Migration
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
            'deposit_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'unique'     => true,
            ],
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'previous_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'new_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'deposit_method' => [
                'type'       => 'ENUM',
                'constraint' => ['Cash', 'Cheque', 'Online Transfer', 'Other'],
                'default'    => 'Cash',
            ],
            'teller_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reference_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addForeignKey('account_id', 'accounts', 'id', 'RESTRICT', 'RESTRICT');
        if ($this->db->tableExists('users')) {
            $this->forge->addForeignKey('teller_id', 'users', 'id', 'SET NULL', 'CASCADE');
        }

        $this->forge->createTable('deposits', true);
    }

    public function down()
    {
        $this->forge->dropTable('deposits', true);
    }
}
