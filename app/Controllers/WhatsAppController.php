<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    public function settings(Request $request): void
    {
        $settings = NotificationService::getWhatsAppSettings();
        $this->view('settings.whatsapp', ['title' => 'Pengaturan WhatsApp', 'settings' => $settings]);
    }

    public function updateSettings(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['api_url', 'api_key', 'sender', 'is_active', 'notify_on_in', 'notify_on_late', 'notify_on_out']);
        $db = Database::connection();

        $existing = NotificationService::getWhatsAppSettings();
        $payload = [
            'api_url' => $data['api_url'] ?: null,
            'api_key' => $data['api_key'] ?: null,
            'sender' => $data['sender'] ?: null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'notify_on_in' => !empty($data['notify_on_in']) ? 1 : 0,
            'notify_on_late' => !empty($data['notify_on_late']) ? 1 : 0,
            'notify_on_out' => !empty($data['notify_on_out']) ? 1 : 0,
        ];

        if ($existing) {
            $stmt = $db->prepare(
                'UPDATE whatsapp_settings SET api_url=:api_url, api_key=:api_key, sender=:sender, is_active=:is_active,
                 notify_on_in=:notify_on_in, notify_on_late=:notify_on_late, notify_on_out=:notify_on_out WHERE id = :id'
            );
            $payload['id'] = $existing['id'];
        } else {
            $stmt = $db->prepare(
                'INSERT INTO whatsapp_settings (api_url, api_key, sender, is_active, notify_on_in, notify_on_late, notify_on_out)
                 VALUES (:api_url, :api_key, :sender, :is_active, :notify_on_in, :notify_on_late, :notify_on_out)'
            );
        }
        $stmt->execute($payload);

        AuditLogService::log('update', 'settings', null, 'Memperbarui pengaturan WhatsApp');
        Session::flash('success', 'Pengaturan WhatsApp berhasil disimpan.');
        $this->redirect('/settings/whatsapp');
    }

    public function sendTestForm(Request $request): void
    {
        $students = Database::connection()->query(
            "SELECT s.id, s.full_name, g.phone FROM students s LEFT JOIN guardians g ON g.id = s.guardian_id WHERE s.status = 'active' ORDER BY s.full_name LIMIT 200"
        )->fetchAll();

        $this->view('whatsapp.send', ['title' => 'Kirim WhatsApp', 'students' => $students]);
    }

    public function sendTest(Request $request): void
    {
        $this->requireCsrf();

        $phone = trim((string) $request->input('phone'));
        $message = trim((string) $request->input('message'));

        if ($phone === '' || $message === '') {
            Session::flash('error', 'Nomor dan pesan wajib diisi.');
            $this->redirect('/whatsapp/send');
            return;
        }

        $db = Database::connection();
        $insert = $db->prepare('INSERT INTO whatsapp_logs (phone_number, message, status) VALUES (:phone, :message, "pending")');
        $insert->execute(['phone' => $phone, 'message' => $message]);
        $logId = (int) $db->lastInsertId();

        WhatsAppService::sendQueuedMessage($logId, $phone, $message);

        AuditLogService::log('whatsapp_test', 'whatsapp', (string) $logId, "Kirim WhatsApp manual ke {$phone}");
        Session::flash('success', 'Pesan diproses. Cek Log WhatsApp untuk status pengiriman.');
        $this->redirect('/whatsapp/send');
    }

    public function logs(Request $request): void
    {
        $status = $request->query('status');
        $where = '';
        $params = [];
        if ($status) {
            $where = 'WHERE status = :status';
            $params['status'] = $status;
        }

        $stmt = Database::connection()->prepare("SELECT * FROM whatsapp_logs {$where} ORDER BY id DESC LIMIT 200");
        $stmt->execute($params);

        $this->view('whatsapp.logs', ['title' => 'Log WhatsApp', 'logs' => $stmt->fetchAll(), 'status' => $status]);
    }

    public function retry(Request $request, string $id): void
    {
        $this->requireCsrf();

        if (WhatsAppService::retry((int) $id)) {
            AuditLogService::log('whatsapp_retry', 'whatsapp', $id, 'Retry pengiriman WhatsApp');
            Session::flash('success', 'Percobaan kirim ulang selesai diproses.');
        } else {
            Session::flash('error', 'Log WhatsApp tidak ditemukan.');
        }

        $this->redirect('/whatsapp/logs');
    }
}
