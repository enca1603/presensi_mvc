<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use PDOException;
use RuntimeException;

class AttendanceInvalidQrException extends RuntimeException {}
class AttendanceInactiveException extends RuntimeException {}
class AttendanceDuplicateException extends RuntimeException {}
class AttendanceCompletedException extends RuntimeException {}
class AttendanceClosedException extends RuntimeException {}
class AttendanceNotSchoolDayException extends RuntimeException {}

class AttendanceService
{
    public static function processScan(string $token, ?int $kioskDeviceId = null, string $method = 'qr'): array
    {
        $token = trim($token);

        if (str_starts_with($token, 'STU|')) {
            $personType = 'student';
            $person = QrCodeService::findActiveStudentByToken($token);
        } elseif (str_starts_with($token, 'TEA|')) {
            $personType = 'teacher';
            $person = QrCodeService::findActiveTeacherByToken($token);
        } else {
            throw new AttendanceInvalidQrException('QR Code tidak valid.');
        }

        if (!$person) {
            throw new AttendanceInvalidQrException('QR Code tidak valid atau sudah tidak aktif.');
        }

        if ($person['status'] !== 'active') {
            throw new AttendanceInactiveException(ucfirst($personType) . ' berstatus tidak aktif.');
        }

        self::assertSchoolDay();

        $settings = self::getSettings($personType);
        $now = new \DateTimeImmutable('now');
        $today = $now->format('Y-m-d');
        $time = $now->format('H:i:s');

        $db = Database::connection();
        $personIdColumn = $personType === 'student' ? 'student_id' : 'teacher_id';
        $personId = (int) $person['id'];

        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                "SELECT type, status FROM attendances WHERE {$personIdColumn} = :pid AND attendance_date = :date FOR UPDATE"
            );
            $stmt->execute(['pid' => $personId, 'date' => $today]);
            $existing = $stmt->fetchAll();
            $hasIn = false;
            $hasOut = false;
            foreach ($existing as $row) {
                if ($row['type'] === 'in') {
                    $hasIn = true;
                }
                if ($row['type'] === 'out') {
                    $hasOut = true;
                }
            }

            if (!$hasIn) {
                $type = 'in';
                if ($time > $settings['day_end_time']) {
                    throw new AttendanceClosedException('Waktu presensi masuk sudah berakhir. Silakan hubungi admin untuk presensi manual.');
                }
                $status = ($time <= $settings['late_after_time']) ? 'present' : 'late';
            } elseif (!$hasOut) {
                if (!$settings['out_enabled']) {
                    throw new AttendanceDuplicateException(ucfirst($personType) . ' sudah melakukan presensi.');
                }
                $type = 'out';
                $status = 'present';
            } else {
                throw new AttendanceCompletedException('Sudah selesai presensi hari ini.');
            }

            $insert = $db->prepare(
                "INSERT INTO attendances ({$personIdColumn}, attendance_date, type, scan_time, status, method, kiosk_device_id)
                 VALUES (:pid, :date, :type, :time, :status, :method, :kiosk)"
            );
            $insert->execute([
                'pid' => $personId,
                'date' => $today,
                'type' => $type,
                'time' => $time,
                'status' => $status,
                'method' => $method,
                'kiosk' => $kioskDeviceId,
            ]);
            $attendanceId = (int) $db->lastInsertId();

            $notificationStatus = null;
            if ($personType === 'student' && $type === 'in') {
                $notificationStatus = NotificationService::queueAttendanceNotification($attendanceId, (int) $person['id'], $status);
            }

            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new AttendanceDuplicateException(ucfirst($personType) . ' sudah melakukan presensi.');
            }
            throw $e;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        AuditLogService::log('attendance', 'attendance', (string) $attendanceId, ucfirst($personType) . " {$person['full_name']} presensi {$type} ({$status})");

        return [
            'person_type' => $personType,
            'person' => $person,
            'attendance' => [
                'id' => $attendanceId,
                'type' => $type,
                'time' => $time,
                'status' => $status,
            ],
            'notification' => ['status' => $notificationStatus ?? 'skipped'],
        ];
    }

    public static function getSettings(string $type): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM attendance_settings WHERE type = :type LIMIT 1');
        $stmt->execute(['type' => $type]);
        $settings = $stmt->fetch();

        if (!$settings) {
            throw new RuntimeException("Pengaturan jam presensi untuk {$type} belum dikonfigurasi.");
        }

        return $settings;
    }

    private static function assertSchoolDay(): void
    {
        $dayOfWeek = (int) (new \DateTimeImmutable('now'))->format('w');
        $stmt = Database::connection()->prepare('SELECT is_active FROM school_days WHERE day_of_week = :d LIMIT 1');
        $stmt->execute(['d' => $dayOfWeek]);
        $row = $stmt->fetch();

        if ($row && !$row['is_active']) {
            throw new AttendanceNotSchoolDayException('Hari ini bukan hari sekolah aktif.');
        }
    }

    public static function recordManual(array $data, int $recordedBy): int
    {
        $personType = $data['person_type'];
        $personIdColumn = $personType === 'student' ? 'student_id' : 'teacher_id';
        $personId = (int) $data['person_id'];

        $db = Database::connection();
        $stmt = $db->prepare(
            "INSERT INTO attendances ({$personIdColumn}, attendance_date, type, scan_time, status, method, notes, recorded_by)
             VALUES (:pid, :date, :type, :time, :status, 'manual', :notes, :recorded_by)"
        );

        try {
            $stmt->execute([
                'pid' => $personId,
                'date' => $data['attendance_date'],
                'type' => $data['type'],
                'time' => $data['scan_time'] ?: date('H:i:s'),
                'status' => $data['status'],
                'notes' => $data['notes'] ?: null,
                'recorded_by' => $recordedBy,
            ]);
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new AttendanceDuplicateException('Data presensi untuk tanggal dan jenis tersebut sudah ada.');
            }
            throw $e;
        }

        $id = (int) $db->lastInsertId();

        AuditLogService::log(
            'manual_attendance',
            'attendance',
            (string) $id,
            "Input presensi manual ({$personType} #{$personId}, {$data['type']}, {$data['status']})",
            null,
            $data
        );

        return $id;
    }

    public static function correct(int $attendanceId, array $newData, string $reason, int $changedBy): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM attendances WHERE id = :id');
        $stmt->execute(['id' => $attendanceId]);
        $old = $stmt->fetch();

        if (!$old) {
            throw new RuntimeException('Data presensi tidak ditemukan.');
        }

        $db->beginTransaction();
        try {
            $update = $db->prepare(
                'UPDATE attendances SET attendance_date = :date, scan_time = :time, status = :status, notes = :notes WHERE id = :id'
            );
            $update->execute([
                'date' => $newData['attendance_date'],
                'time' => $newData['scan_time'],
                'status' => $newData['status'],
                'notes' => $newData['notes'] ?: null,
                'id' => $attendanceId,
            ]);

            $log = $db->prepare(
                'INSERT INTO attendance_corrections (attendance_id, old_data, new_data, reason, changed_by) VALUES (:aid, :old, :new, :reason, :by)'
            );
            $log->execute([
                'aid' => $attendanceId,
                'old' => json_encode($old, JSON_UNESCAPED_UNICODE),
                'new' => json_encode($newData, JSON_UNESCAPED_UNICODE),
                'reason' => $reason,
                'by' => $changedBy,
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        AuditLogService::log('correction', 'attendance', (string) $attendanceId, "Koreksi presensi: {$reason}", $old, $newData);
    }
}
