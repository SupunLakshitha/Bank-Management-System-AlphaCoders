<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
    protected $table            = 'branches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'name',
        'code',
        'location',
    ];

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'code' => 'required|min_length[2]|max_length[10]|is_unique[branches.code,id,{id}]',
    ];
}
