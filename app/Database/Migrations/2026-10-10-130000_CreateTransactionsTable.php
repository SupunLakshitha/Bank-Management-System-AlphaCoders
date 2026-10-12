<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
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
            'tx_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'unique'     => true,
            ],
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'DEPOSIT',
                    'WITHDRAWAL',
                    'TRANSFER',
                    'LOAN_DISBURSEMENT',
                    'LOAN_REPAYMENT',
                    'INTEREST_CREDIT',
                    'FEE',
                    'ADJUSTMENT',
                ],
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'tx_date' => [
                'type' => 'DATE',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'reference_no' => [
                'type'       => 'VARCHAR',
                'constraint' => '40',
                'null'       => true,
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
        $this->forge->addKey('type');
        $this->forge->addKey('tx_date');
        $this->forge->addForeignKey('account_id', 'accounts', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}
