<?php

namespace App\Services;

use App\Core\Database;

/**
 * Abstraction layer for sending WhatsApp messages. Controllers and other
 * services must never call the WhatsApp provider API directly — always
 * go through this service so the provider can be swapped without
 * touching business logic.
 */
class WhatsAppService
{
    public static function sendMessage(string $phone, string $message): array
    {
        $settings = NotificationService::getWhatsAppSettings();

        if (!$settings || empty($settings['api_url']) || empty($settings['api_key'])) {
            return ['success' => false, 'response' => 'WhatsApp API belum dikonfigurasi.'];
        }

        $payload = json_encode([
            'target' => $phone,
            'message' => $message,
            'sender' => $settings['sender'],
        ]);

        $ch = curl_init($settings['api_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $settings['api_key'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            return ['success' => false, 'response' => "cURL error: {$error}"];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $response ?: "HTTP {$httpCode}"];
        }

        return ['success' => false, 'response' => "HTTP {$httpCode}: " . ($response ?: 'no body')];
    }

    public static function sendQueuedMessage(int $logId, string $phone, string $message): void
    {
        $db = Database::connection();
        $result = self::sendMessage($phone, $message);

        $stmt = $db->prepare(
            'UPDATE whatsapp_logs
             SET status = :status, provider_response = :response, attempt_count = attempt_count + 1, sent_at = :sent_at
             WHERE id = :id'
        );
        $stmt->execute([
            'status' => $result['success'] ? 'sent' : 'failed',
            'response' => mb_substr($result['response'], 0, 2000),
            'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null,
            'id' => $logId,
        ]);
    }

    public static function retry(int $logId): bool
    {
        $stmt = Database::connection()->prepare('SELECT phone_number, message FROM whatsapp_logs WHERE id = :id');
        $stmt->execute(['id' => $logId]);
        $log = $stmt->fetch();

        if (!$log) {
            return false;
        }

        self::sendQueuedMessage($logId, $log['phone_number'], $log['message']);
        return true;
    }
}
