<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductAndAccountToFixedDeposits extends Migration
{
    public function up()
    {
        $this->forge->addColumn('fixed_deposits', [
            'fd_product_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'customer_id'],
            'account_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'fd_product_id'],
        ]);

        $this->db->query('ALTER TABLE fixed_deposits ADD CONSTRAINT fk_fd_product FOREIGN KEY (fd_product_id) REFERENCES fd_products(id) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE fixed_deposits DROP FOREIGN KEY fk_fd_product');
        $this->forge->dropColumn('fixed_deposits', ['fd_product_id', 'account_id']);
    }
}