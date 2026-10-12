<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table            = 'accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'account_no',
        'customer_id',
        'branch_id',
        'gl_account_id',
        'account_type',
        'currency',
        'balance',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findSavingsForCustomer(int $customerId): ?array
    {
        return $this->where('customer_id', $customerId)
            ->where('account_type', 'SAVINGS')
            ->first();
    }

    public function ensureSavingsAccount(int $customerId, int $branchId, ?string $accountNo = null): array
    {
        $existing = $this->findSavingsForCustomer($customerId);
        if ($existing) {
            if ((int) $existing['branch_id'] !== $branchId) {
                $this->update($existing['id'], ['branch_id' => $branchId]);
                $existing['branch_id'] = $branchId;
            }

            return $existing;
        }

        $gl = $this->db->table('gl_accounts')->where('code', 'GL-20100')->get()->getRowArray();

        $this->insert([
            'account_no'    => $accountNo ?: ('SA-' . str_pad((string) $customerId, 6, '0', STR_PAD_LEFT)),
            'customer_id'   => $customerId,
            'branch_id'     => $branchId,
            'gl_account_id' => $gl['id'] ?? null,
            'account_type'  => 'SAVINGS',
            'currency'      => 'LKR',
            'balance'       => 0,
            'status'        => 'Active',
        ]);

        return $this->find($this->getInsertID());
    }
}
