<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tx_id',
        'account_id',
        'counter_account_id',
        'type',
        'amount',
        'tx_date',
        'description',
        'reference_no',
        'branch_id',
        'created_by',
        'status',
    ];

    protected $useTimestamps = false;
}
