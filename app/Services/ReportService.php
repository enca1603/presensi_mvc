<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class ReportService
{
    public static function daily(string $date, ?int $classId = null, ?int $majorId = null): array
    {
        $conditions = ['s.status = "active"'];
        $params = ['date1' => $date, 'date2' => $date];

        if ($classId) {
            $conditions[] = 's.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        if ($majorId) {
            $conditions[] = 's.major_id = :major_id';
            $params['major_id'] = $majorId;
        }
        $where = implode(' AND ', $conditions);

        $sql = "SELECT s.id, s.nis, s.full_name, c.name AS class_name,
                       ai.status AS status_in, ai.scan_time AS time_in,
                       ao.scan_time AS time_out
                FROM students s
                LEFT JOIN classes c ON c.id = s.class_id
                LEFT JOIN attendances ai ON ai.student_id = s.id AND ai.attendance_date = :date1 AND ai.type = 'in'
                LEFT JOIN attendances ao ON ao.student_id = s.id AND ao.attendance_date = :date2 AND ao.type = 'out'
                WHERE {$where}
                ORDER BY c.name, s.full_name";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['status_final'] = $row['status_in'] ?? 'absent';
        }

        return $rows;
    }

    public static function monthly(int $year, int $month, ?int $classId = null): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $conditions = ['s.status = "active"'];
        $params = ['start' => $start, 'end' => $end];
        if ($classId) {
            $conditions[] = 's.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        $where = implode(' AND ', $conditions);

        $sql = "SELECT s.id, s.nis, s.full_name, c.name AS class_name,
                       SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                       SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                       SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) AS permission_count,
                       SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) AS sick_count,
                       SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count
                FROM students s
                LEFT JOIN classes c ON c.id = s.class_id
                LEFT JOIN attendances a ON a.student_id = s.id AND a.type = 'in' AND a.attendance_date BETWEEN :start AND :end
                WHERE {$where}
                GROUP BY s.id, s.nis, s.full_name, c.name
                ORDER BY c.name, s.full_name";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function student(int $studentId, string $startDate, string $endDate): array
    {
        $db = Database::connection();

        $stmt = $db->prepare(
            "SELECT s.*, c.name AS class_name, m.name AS major_name, g.name AS guardian_name
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN majors m ON m.id = s.major_id
             LEFT JOIN guardians g ON g.id = s.guardian_id
             WHERE s.id = :id"
        );
        $stmt->execute(['id' => $studentId]);
        $student = $stmt->fetch();

        $historyStmt = $db->prepare(
            "SELECT attendance_date, type, scan_time, status, method
             FROM attendances WHERE student_id = :id AND attendance_date BETWEEN :start AND :end
             ORDER BY attendance_date DESC, scan_time DESC"
        );
        $historyStmt->execute(['id' => $studentId, 'start' => $startDate, 'end' => $endDate]);
        $history = $historyStmt->fetchAll();

        $summaryStmt = $db->prepare(
            "SELECT status, COUNT(*) c FROM attendances
             WHERE student_id = :id AND type = 'in' AND attendance_date BETWEEN :start AND :end
             GROUP BY status"
        );
        $summaryStmt->execute(['id' => $studentId, 'start' => $startDate, 'end' => $endDate]);
        $summary = array_fill_keys(['present', 'late', 'permission', 'sick', 'absent'], 0);
        foreach ($summaryStmt->fetchAll() as $row) {
            $summary[$row['status']] = (int) $row['c'];
        }

        return ['student' => $student, 'history' => $history, 'summary' => $summary];
    }

    public static function teacher(int $teacherId, string $startDate, string $endDate): array
    {
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $teacherId]);
        $teacher = $stmt->fetch();

        $historyStmt = $db->prepare(
            "SELECT attendance_date, type, scan_time, status, method
             FROM attendances WHERE teacher_id = :id AND attendance_date BETWEEN :start AND :end
             ORDER BY attendance_date DESC, scan_time DESC"
        );
        $historyStmt->execute(['id' => $teacherId, 'start' => $startDate, 'end' => $endDate]);
        $history = $historyStmt->fetchAll();

        $summaryStmt = $db->prepare(
            "SELECT status, COUNT(*) c FROM attendances
             WHERE teacher_id = :id AND type = 'in' AND attendance_date BETWEEN :start AND :end
             GROUP BY status"
        );
        $summaryStmt->execute(['id' => $teacherId, 'start' => $startDate, 'end' => $endDate]);
        $summary = array_fill_keys(['present', 'late', 'permission', 'sick', 'absent', 'official_duty'], 0);
        foreach ($summaryStmt->fetchAll() as $row) {
            $summary[$row['status']] = (int) $row['c'];
        }

        return ['teacher' => $teacher, 'history' => $history, 'summary' => $summary];
    }

    public static function classReport(int $classId, string $startDate, string $endDate): array
    {
        $db = Database::connection();

        $stmt = $db->prepare('SELECT c.*, m.name AS major_name FROM classes c LEFT JOIN majors m ON m.id = c.major_id WHERE c.id = :id');
        $stmt->execute(['id' => $classId]);
        $class = $stmt->fetch();

        $sql = "SELECT s.id, s.nis, s.full_name,
                       SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                       SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                       SUM(CASE WHEN a.status = 'permission' THEN 1 ELSE 0 END) AS permission_count,
                       SUM(CASE WHEN a.status = 'sick' THEN 1 ELSE 0 END) AS sick_count,
                       SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count
                FROM students s
                LEFT JOIN attendances a ON a.student_id = s.id AND a.type = 'in' AND a.attendance_date BETWEEN :start AND :end
                WHERE s.class_id = :class_id AND s.status = 'active'
                GROUP BY s.id, s.nis, s.full_name
                ORDER BY s.full_name";
        $stmt = $db->prepare($sql);
        $stmt->execute(['class_id' => $classId, 'start' => $startDate, 'end' => $endDate]);
        $students = $stmt->fetchAll();

        return ['class' => $class, 'students' => $students];
    }

    public static function schoolInfo(): array
    {
        $school = Database::connection()->query('SELECT * FROM school_settings ORDER BY id LIMIT 1')->fetch();
        return $school ?: ['school_name' => 'Sistem Presensi Sekolah', 'address' => '', 'logo' => null];
    }
}
