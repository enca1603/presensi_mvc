<?php

namespace App\Services;

use App\Core\Database;

class NotificationService
{
    public static function queueAttendanceNotification(int $attendanceId, int $studentId, string $status): string
    {
        $db = Database::connection();

        $settings = self::getWhatsAppSettings();
        if (!$settings || !$settings['is_active']) {
            return 'disabled';
        }

        if ($status === 'late' && !$settings['notify_on_late']) {
            return 'skipped';
        }
        if ($status === 'present' && !$settings['notify_on_in']) {
            return 'skipped';
        }

        $stmt = $db->prepare(
            "SELECT s.full_name, s.nis, c.name AS class_name, g.phone
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN guardians g ON g.id = s.guardian_id
             WHERE s.id = :id"
        );
        $stmt->execute(['id' => $studentId]);
        $student = $stmt->fetch();

        if (!$student || empty($student['phone'])) {
            return 'skipped_no_phone';
        }

        $message = self::buildAttendanceMessage($student, $status);

        $insert = $db->prepare(
            'INSERT INTO whatsapp_logs (attendance_id, phone_number, message, status) VALUES (:aid, :phone, :message, "pending")'
        );
        $insert->execute([
            'aid' => $attendanceId,
            'phone' => $student['phone'],
            'message' => $message,
        ]);

        return 'queued';
    }

    public static function buildAttendanceMessage(array $student, string $status): string
    {
        $date = (new \DateTimeImmutable('now'))->format('d/m/Y');
        $time = (new \DateTimeImmutable('now'))->format('H:i:s');
        $statusLabel = $status === 'late' ? 'TERLAMBAT' : 'HADIR';

        if ($status === 'late') {
            return "Assalamu'alaikum.\n"
                . "Kami informasikan bahwa:\n"
                . "Nama Siswa: {$student['full_name']}\n"
                . "Kelas: " . ($student['class_name'] ?? '-') . "\n"
                . "telah melakukan presensi pada:\n"
                . "Tanggal: {$date}\n"
                . "Jam: {$time}\n"
                . "Status: TERLAMBAT\n"
                . "Mohon menjadi perhatian Bapak/Ibu.\n"
                . "Terima kasih.";
        }

        return "Assalamu'alaikum.\n"
            . "Yth. Bapak/Ibu Orang Tua/Wali.\n"
            . "Kami informasikan bahwa:\n"
            . "Nama Siswa: {$student['full_name']}\n"
            . "NIS: {$student['nis']}\n"
            . "Kelas: " . ($student['class_name'] ?? '-') . "\n"
            . "telah melakukan presensi masuk.\n"
            . "Tanggal: {$date}\n"
            . "Jam: {$time}\n"
            . "Status: {$statusLabel}\n"
            . "Pesan ini dikirim otomatis oleh Sistem Presensi Sekolah.\n"
            . "Terima kasih.";
    }

    public static function getWhatsAppSettings(): ?array
    {
        $stmt = Database::connection()->query('SELECT * FROM whatsapp_settings ORDER BY id LIMIT 1');
        return $stmt->fetch() ?: null;
    }

    public static function dispatchPending(int $limit = 20): void
    {
        $stmt = Database::connection()->prepare(
            "SELECT id, phone_number, message FROM whatsapp_logs WHERE status = 'pending' ORDER BY id ASC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        foreach ($stmt->fetchAll() as $log) {
            WhatsAppService::sendQueuedMessage((int) $log['id'], $log['phone_number'], $log['message']);
        }
    }
}
