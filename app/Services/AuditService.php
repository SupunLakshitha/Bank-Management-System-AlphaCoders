<?php

namespace App\Services;

use App\Models\AuditLogModel;

class AuditService
{
    public static function log(string $module, string $action, ?string $referenceId = null, ?string $details = null): bool
    {
        try {
            $session = session();
            return (bool) (new AuditLogModel())->insert([
                'user_id' => $session->get('user_id'),
                'username' => $session->get('username'),
                'role' => $session->get('role'),
                'module' => $module,
                'action' => $action,
                'reference_id' => $referenceId,
                'details' => $details,
                'ip_address' => service('request')->getIPAddress(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Unable to write audit log: {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
