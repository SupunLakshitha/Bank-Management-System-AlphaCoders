<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id', 'username', 'role', 'module', 'action', 'reference_id',
        'details', 'ip_address', 'created_at',
    ];
    protected $useTimestamps = false;

    public function getLogsFiltered($module = null, $action = null, $startDate = null, $endDate = null, $limit = 100)
    {
        $builder = $this->orderBy('created_at', 'DESC');
        if ($module) $builder->where('module', $module);
        if ($action) $builder->where('action', $action);
        if ($startDate) $builder->where('created_at >=', $startDate . ' 00:00:00');
        if ($endDate) $builder->where('created_at <=', $endDate . ' 23:59:59');
        return $builder->findAll(max(1, min((int) $limit, 1000)));
    }
}
