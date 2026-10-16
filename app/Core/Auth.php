<?php

namespace App\Core;

use PDO;

class Auth
{
    private static ?array $userCache = null;
    private static ?array $permissionsCache = null;

    public static function attempt(string $username, string $password): array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE (u.username = :username OR u.email = :email) AND u.status = "active"
             LIMIT 1'
        );
        $stmt->execute(['username' => $username, 'email' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Username atau password salah.'];
        }

        Session::regenerate(true);

        Session::set('user_id', (int) $user['id']);
        Session::set('role_id', (int) $user['role_id']);
        Session::set('role_slug', $user['role_slug']);
        Session::set('role_name', $user['role_name']);
        Session::set('full_name', $user['full_name']);
        Session::set('logged_in_at', time());

        $update = $db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $update->execute(['id' => $user['id']]);

        self::$userCache = null;

        return ['success' => true, 'user' => $user];
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function roleSlug(): ?string
    {
        return Session::get('role_slug');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$userCache !== null) {
            return self::$userCache;
        }

        $stmt = Database::connection()->prepare(
            'SELECT u.id, u.username, u.email, u.full_name, u.photo, u.status, u.role_id, r.name AS role_name, r.slug AS role_slug
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => self::id()]);
        $user = $stmt->fetch();

        self::$userCache = $user ?: null;

        return self::$userCache;
    }

    public static function isSuperAdmin(): bool
    {
        return self::roleSlug() === 'super_admin';
    }

    public static function can(string $permissionSlug): bool
    {
        if (!self::check()) {
            return false;
        }

        if (self::isSuperAdmin()) {
            return true;
        }

        if (self::$permissionsCache === null) {
            $stmt = Database::connection()->prepare(
                'SELECT p.slug FROM permissions p
                 JOIN role_permissions rp ON rp.permission_id = p.id
                 WHERE rp.role_id = :role_id'
            );
            $stmt->execute(['role_id' => Session::get('role_id')]);
            self::$permissionsCache = array_column($stmt->fetchAll(), 'slug');
        }

        return in_array($permissionSlug, self::$permissionsCache, true);
    }

    public static function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array(self::roleSlug(), $roles, true);
    }

    public static function logout(): void
    {
        self::$userCache = null;
        self::$permissionsCache = null;
        Session::destroy();
    }
}
