<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransferCounterAccount extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE `transactions`
             ADD COLUMN `counter_account_id` INT(11) UNSIGNED NULL AFTER `account_id`,
             ADD CONSTRAINT `transactions_counter_account_id_foreign`
             FOREIGN KEY (`counter_account_id`) REFERENCES `accounts` (`id`)
             ON DELETE SET NULL ON UPDATE RESTRICT'
        );

        $this->db->query(
            'UPDATE `transactions` t
             INNER JOIN `transaction_entries` e
                ON e.transaction_id = t.id AND e.credit > 0 AND e.account_id IS NOT NULL
             SET t.counter_account_id = e.account_id
             WHERE t.type = \'TRANSFER\' AND t.counter_account_id IS NULL'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `transactions` DROP FOREIGN KEY `transactions_counter_account_id_foreign`');
        $this->db->query('ALTER TABLE `transactions` DROP COLUMN `counter_account_id`');
    }
}
