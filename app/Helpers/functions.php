<?php

use App\Core\Csrf;
use App\Core\Session;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($cache[$file])) {
            $configPath = dirname(__DIR__, 2) . '/config/' . $file . '.php';
            $cache[$file] = is_file($configPath) ? require $configPath : [];
        }

        if ($path === null) {
            return $cache[$file];
        }

        return $cache[$file][$path] ?? $default;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::getFlash('_old_input', []);
        return $old[$key] ?? $default;
    }
}

if (!function_exists('flash_old')) {
    function flash_old(array $data): void
    {
        Session::flash('_old_input', $data);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $default = null): ?string
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token_value')) {
    function csrf_token_value(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '-';
        }
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '-';
    }
}

if (!function_exists('format_date_id')) {
    function format_date_id(?string $date): string
    {
        if (!$date) {
            return '-';
        }
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return '-';
        }
        return $days[(int) date('w', $timestamp)] . ', ' . date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

if (!function_exists('active_menu')) {
    function active_menu(string $path): string
    {
        $uri = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
        return str_starts_with($uri, $path) ? 'active' : '';
    }
}

if (!function_exists('random_token')) {
    function random_token(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
