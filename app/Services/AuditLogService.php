<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

class AuditLogService
{
    public static function log(
        string $action,
        string $module,
        ?string $recordId = null,
        ?string $description = null,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $stmt = Database::connection()->prepare(
            'INSERT INTO audit_logs (user_id, action, module, record_id, description, old_data, new_data, ip_address, user_agent)
             VALUES (:user_id, :action, :module, :record_id, :description, :old_data, :new_data, :ip, :ua)'
        );

        $stmt->execute([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }
}
