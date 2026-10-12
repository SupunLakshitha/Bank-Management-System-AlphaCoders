<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountTypeModel extends Model
{
    protected $table            = 'account_types';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name',
        'code',
        'interest_rate',
        'min_balance',
        'description',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getActiveTypes(): array
    {
        return $this->where('is_active', 1)->findAll();
    }
}
