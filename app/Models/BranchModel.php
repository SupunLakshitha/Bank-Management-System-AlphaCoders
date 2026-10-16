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
    protected $protectFields    = true;

    public function withCounts(?int $onlyBranchId = null): array
    {
        $sql = 'SELECT b.*,
                    (SELECT COUNT(*) FROM users u
                        INNER JOIN roles r ON r.id = u.role_id
                        WHERE u.branch_id = b.id AND r.name <> \'Customer\') AS employee_count,
                    (SELECT COUNT(*) FROM customers c WHERE c.branch_id = b.id) AS customer_count,
                    (SELECT COUNT(*) FROM accounts a WHERE a.branch_id = b.id) AS account_count,
                    (SELECT COUNT(*) FROM transactions t WHERE t.branch_id = b.id) AS transaction_count,
                    (SELECT COUNT(*) FROM loans l WHERE l.branch_id = b.id) AS loan_count
                FROM branches b';

        if ($onlyBranchId) {
            $sql .= ' WHERE b.id = ?';
        }

        $sql .= ' ORDER BY b.name ASC';

        $query = $onlyBranchId
            ? $this->db->query($sql, [$onlyBranchId])
            : $this->db->query($sql);

        return $query->getResultArray();
    }
}
