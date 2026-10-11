<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'user_id',
        'branch_id',
        'customer_id',
        'full_name',
        'nic',
        'dob',
        'phone',
        'address',
        'occupation',
        'kyc_status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'customer_id' => 'required|min_length[3]|max_length[20]',
        'full_name'   => 'required|min_length[3]|max_length[255]',
    ];
}
