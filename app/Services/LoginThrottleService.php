<?php

namespace App\Services;

use App\Core\Database;

/**
 * Simple brute-force protection: blocks further login attempts for a
 * username after too many failures within a short window. Backed by
 * audit_logs (login_failed entries) so no extra schema is needed.
 */
class LoginThrottleService
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    public static function tooManyAttempts(string $username): bool
    {
        return self::recentFailedCount($username) >= self::MAX_ATTEMPTS;
    }

    public static function remainingSeconds(string $username): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT created_at FROM audit_logs
             WHERE action = 'login_failed' AND description = :desc
             ORDER BY id DESC LIMIT :offset, 1"
        );
        $stmt->bindValue('desc', self::describe($username));
        $stmt->bindValue('offset', self::MAX_ATTEMPTS - 1, \PDO::PARAM_INT);
        $stmt->execute();
        $oldest = $stmt->fetch()['created_at'] ?? null;

        if (!$oldest) {
            return 0;
        }

        $unlockAt = strtotime($oldest) + self::WINDOW_MINUTES * 60;
        return max(0, $unlockAt - time());
    }

    private static function recentFailedCount(string $username): int
    {
        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) c FROM audit_logs
             WHERE action = 'login_failed' AND description = :desc
             AND created_at >= (NOW() - INTERVAL :minutes MINUTE)"
        );
        $stmt->bindValue('desc', self::describe($username));
        $stmt->bindValue('minutes', self::WINDOW_MINUTES, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch()['c'];
    }

    public static function describe(string $username): string
    {
        return "Percobaan login gagal untuk username: {$username}";
    }
}
