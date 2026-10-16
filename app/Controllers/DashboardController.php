<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::connection();
        $today = date('Y-m-d');

        $totalStudents = (int) $db->query("SELECT COUNT(*) c FROM students WHERE status = 'active'")->fetch()['c'];
        $totalTeachers = (int) $db->query("SELECT COUNT(*) c FROM teachers WHERE status = 'active'")->fetch()['c'];

        $statusCounts = self::todayStatusCounts($db, $today);
        $hadirHariIni = $statusCounts['present'] + $statusCounts['late'];
        $belumHadir = max(0, $totalStudents - array_sum($statusCounts));

        $this->view('dashboard.index', [
            'title' => 'Dashboard',
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'hadirHariIni' => $hadirHariIni,
            'terlambat' => $statusCounts['late'],
            'izin' => $statusCounts['permission'],
            'sakit' => $statusCounts['sick'],
            'alpa' => $statusCounts['absent'],
            'belumHadir' => $belumHadir,
            'trend' => self::weeklyTrend($db),
            'statusDistribution' => self::weeklyStatusDistribution($db),
            'perClass' => self::weeklyPerClass($db),
            'lateness' => self::weeklyLateness($db),
            'showDefaultPasswordWarning' => \App\Services\SecurityCheckService::isUsingDefaultPassword(),
        ]);
    }

    public function stats(Request $request): void
    {
        $db = Database::connection();
        $today = date('Y-m-d');

        $totalStudents = (int) $db->query("SELECT COUNT(*) c FROM students WHERE status = 'active'")->fetch()['c'];
        $statusCounts = self::todayStatusCounts($db, $today);
        $belumHadir = max(0, $totalStudents - array_sum($statusCounts));

        $this->json([
            'success' => true,
            'data' => [
                'hadir' => $statusCounts['present'] + $statusCounts['late'],
                'terlambat' => $statusCounts['late'],
                'izin' => $statusCounts['permission'],
                'sakit' => $statusCounts['sick'],
                'alpa' => $statusCounts['absent'],
                'belum_hadir' => $belumHadir,
            ],
        ]);
    }

    private static function todayStatusCounts(\PDO $db, string $today): array
    {
        $statusCounts = ['present' => 0, 'late' => 0, 'permission' => 0, 'sick' => 0, 'absent' => 0];
        $stmt = $db->prepare(
            "SELECT status, COUNT(DISTINCT student_id) c FROM attendances
             WHERE attendance_date = :d AND type = 'in' AND student_id IS NOT NULL
             GROUP BY status"
        );
        $stmt->execute(['d' => $today]);
        foreach ($stmt->fetchAll() as $row) {
            $statusCounts[$row['status']] = (int) $row['c'];
        }

        return $statusCounts;
    }

    private static function weeklyTrend(\PDO $db): array
    {
        $start = (new \DateTimeImmutable('-6 days'))->format('Y-m-d');
        $stmt = $db->prepare(
            "SELECT attendance_date, COUNT(DISTINCT student_id) c FROM attendances
             WHERE type = 'in' AND status IN ('present','late') AND student_id IS NOT NULL AND attendance_date >= :start
             GROUP BY attendance_date"
        );
        $stmt->execute(['start' => $start]);
        $byDate = array_column($stmt->fetchAll(), 'c', 'attendance_date');

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $labels[] = (new \DateTimeImmutable($date))->format('d/m');
            $values[] = (int) ($byDate[$date] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private static function weeklyStatusDistribution(\PDO $db): array
    {
        $start = (new \DateTimeImmutable('-6 days'))->format('Y-m-d');
        $stmt = $db->prepare(
            "SELECT status, COUNT(*) c FROM attendances
             WHERE type = 'in' AND student_id IS NOT NULL AND attendance_date >= :start
             GROUP BY status"
        );
        $stmt->execute(['start' => $start]);
        $counts = array_column($stmt->fetchAll(), 'c', 'status');

        $labels = ['present' => 'Hadir', 'late' => 'Terlambat', 'permission' => 'Izin', 'sick' => 'Sakit', 'absent' => 'Alpa'];
        $values = [];
        foreach ($labels as $key => $label) {
            $values[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => array_values($labels), 'values' => $values];
    }

    private static function weeklyPerClass(\PDO $db): array
    {
        $start = (new \DateTimeImmutable('-6 days'))->format('Y-m-d');
        $stmt = $db->prepare(
            "SELECT c.name, COUNT(CASE WHEN a.status IN ('present','late') THEN 1 END) hadir
             FROM classes c
             LEFT JOIN students s ON s.class_id = c.id AND s.status = 'active'
             LEFT JOIN attendances a ON a.student_id = s.id AND a.type = 'in' AND a.attendance_date >= :start
             WHERE c.status = 'active'
             GROUP BY c.id, c.name
             ORDER BY c.name"
        );
        $stmt->execute(['start' => $start]);
        $rows = $stmt->fetchAll();

        return ['labels' => array_column($rows, 'name'), 'values' => array_map('intval', array_column($rows, 'hadir'))];
    }

    private static function weeklyLateness(\PDO $db): array
    {
        $start = (new \DateTimeImmutable('-6 days'))->format('Y-m-d');
        $stmt = $db->prepare(
            "SELECT attendance_date, COUNT(*) c FROM attendances
             WHERE type = 'in' AND status = 'late' AND student_id IS NOT NULL AND attendance_date >= :start
             GROUP BY attendance_date"
        );
        $stmt->execute(['start' => $start]);
        $byDate = array_column($stmt->fetchAll(), 'c', 'attendance_date');

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $labels[] = (new \DateTimeImmutable($date))->format('d/m');
            $values[] = (int) ($byDate[$date] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
