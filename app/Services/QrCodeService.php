<?php

namespace App\Services;

use App\Core\Database;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use RuntimeException;

class QrCodeService
{
    public static function generateForStudent(int $studentId): string
    {
        return self::generate('student_qr_codes', 'student_id', $studentId, 'STU');
    }

    public static function generateForTeacher(int $teacherId): string
    {
        return self::generate('teacher_qr_codes', 'teacher_id', $teacherId, 'TEA');
    }

    private static function generate(string $table, string $column, int $id, string $prefix): string
    {
        $db = Database::connection();
        $token = $prefix . '|' . bin2hex(random_bytes(20));

        $db->beginTransaction();
        try {
            $revoke = $db->prepare("UPDATE {$table} SET status = 'revoked', revoked_at = NOW() WHERE {$column} = :id AND status = 'active'");
            $revoke->execute(['id' => $id]);

            $insert = $db->prepare("INSERT INTO {$table} ({$column}, token, status, generated_at) VALUES (:id, :token, 'active', NOW())");
            $insert->execute(['id' => $id, 'token' => $token]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new RuntimeException('Gagal membuat QR Code: ' . $e->getMessage());
        }

        return $token;
    }

    public static function getActiveToken(string $table, string $column, int $id): ?string
    {
        $stmt = Database::connection()->prepare("SELECT token FROM {$table} WHERE {$column} = :id AND status = 'active' LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row['token'] ?? null;
    }

    public static function findActiveStudentByToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.* FROM students s
             JOIN student_qr_codes q ON q.student_id = s.id
             WHERE q.token = :token AND q.status = 'active' LIMIT 1"
        );
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public static function findActiveTeacherByToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare(
            "SELECT t.* FROM teachers t
             JOIN teacher_qr_codes q ON q.teacher_id = t.id
             WHERE q.token = :token AND q.status = 'active' LIMIT 1"
        );
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public static function renderPng(string $token, int $size = 400): string
    {
        $result = (new Builder())->build(
            data: $token,
            size: $size,
            margin: 16,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            foregroundColor: new Color(24, 35, 27),
            backgroundColor: new Color(255, 255, 255),
        );

        return $result->getString();
    }
}
