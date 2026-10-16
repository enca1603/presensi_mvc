<?php

namespace App\Core;

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        self::sendAndContinue($data, $statusCode);
        exit;
    }

    /**
     * Sends the JSON response to the client immediately (flushing the
     * buffer) without terminating the script, so the caller can keep
     * doing work — e.g. dispatching a WhatsApp notification — without
     * making the client wait for it. Caller is responsible for exit().
     */
    public static function sendAndContinue(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        header('Content-Length: ' . strlen($body));
        header('Connection: close');
        echo $body;

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }

    public static function success(mixed $data = null, string $message = 'Berhasil', string $status = 'success', int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'Terjadi kesalahan', string $status = 'error', int $statusCode = 400, mixed $data = null): void
    {
        self::json([
            'success' => false,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    public static function download(string $path, string $filename): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public static function notFound(): void
    {
        http_response_code(404);
        require dirname(__DIR__) . '/Views/errors/404.php';
        exit;
    }

    public static function forbidden(): void
    {
        http_response_code(403);
        require dirname(__DIR__) . '/Views/errors/403.php';
        exit;
    }
}
