<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCustomersTable extends Migration
{
    public function up()
    {
        // 1. Modify user_id to be nullable so customers can be registered without immediate online account
        $fieldsToModify = [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];
        $this->forge->modifyColumn('customers', $fieldsToModify);

        // 2. Add email and branch_id columns if they do not exist
        $fieldsToAdd = [];
        if (!$this->db->fieldExists('email', 'customers')) {
            $fieldsToAdd['email'] = [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'after'      => 'phone',
            ];
        }

        if (!$this->db->fieldExists('branch_id', 'customers')) {
            $fieldsToAdd['branch_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'kyc_status',
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('customers', $fieldsToAdd);
        }

        // Add foreign key for branch_id if branches table exists
        if ($this->db->tableExists('branches') && $this->db->fieldExists('branch_id', 'customers')) {
            // Check if constraint exists, if not add
            try {
                $this->db->query('ALTER TABLE customers ADD CONSTRAINT fk_customers_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL ON UPDATE CASCADE');
            } catch (\Throwable $e) {
                // Ignore if already added
            }
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('branch_id', 'customers')) {
            try {
                $this->db->query('ALTER TABLE customers DROP FOREIGN KEY fk_customers_branch');
            } catch (\Throwable $e) {
            }
            $this->forge->dropColumn('customers', 'branch_id');
        }

        if ($this->db->fieldExists('email', 'customers')) {
            $this->forge->dropColumn('customers', 'email');
        }
    }
}
