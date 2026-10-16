<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

class SecurityCheckService
{
    private const SEEDED_DEFAULTS = [
        'admin' => 'admin123',
        'tu' => 'tu12345',
        'guru1' => 'guru12345',
        'siswa1' => 'siswa12345',
    ];

    public static function isUsingDefaultPassword(): bool
    {
        $user = Auth::user();
        if (!$user || !isset(self::SEEDED_DEFAULTS[$user['username']])) {
            return false;
        }

        $stmt = Database::connection()->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute(['id' => Auth::id()]);
        $row = $stmt->fetch();

        return $row && password_verify(self::SEEDED_DEFAULTS[$user['username']], $row['password']);
    }
}
