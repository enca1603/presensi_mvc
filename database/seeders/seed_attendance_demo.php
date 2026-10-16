<?php
// Generates realistic historical attendance data for the last 14 school
// days so the Dashboard charts and Reports pages have something to show.
// Safe to re-run: it deletes attendance rows in the target date range first.

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

$config = require dirname(__DIR__, 2) . '/config/database.php';
$dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $config['driver'], $config['host'], $config['port'], $config['database'], $config['charset']);
$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

$days = 14;
$startDate = (new DateTimeImmutable("-{$days} days"))->format('Y-m-d');

$pdo->prepare('DELETE FROM attendances WHERE attendance_date >= :start')->execute(['start' => $startDate]);

$activeDaysStmt = $pdo->query('SELECT day_of_week FROM school_days WHERE is_active = 1');
$activeDays = $activeDaysStmt->fetchAll(PDO::FETCH_COLUMN);

$students = $pdo->query("SELECT id FROM students WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);
$teachers = $pdo->query("SELECT id FROM teachers WHERE status = 'active'")->fetchAll(PDO::FETCH_COLUMN);

$studentSettings = $pdo->query("SELECT * FROM attendance_settings WHERE type = 'student'")->fetch();
$teacherSettings = $pdo->query("SELECT * FROM attendance_settings WHERE type = 'teacher'")->fetch();

function randomTimeBetween(string $start, string $end): string
{
    $startTs = strtotime($start);
    $endTs = strtotime($end);
    return date('H:i:s', random_int($startTs, $endTs));
}

$insert = $pdo->prepare(
    'INSERT INTO attendances (student_id, teacher_id, attendance_date, type, scan_time, status, method)
     VALUES (:student_id, :teacher_id, :date, :type, :time, :status, "qr")'
);

$inserted = 0;
$today = new DateTimeImmutable('today');

for ($i = $days; $i >= 1; $i--) {
    $date = $today->modify("-{$i} days");
    $dow = (int) $date->format('w');
    if (!in_array($dow, $activeDays, true)) {
        continue;
    }
    $dateStr = $date->format('Y-m-d');

    foreach ($students as $studentId) {
        $roll = random_int(1, 100);
        if ($roll <= 78) {
            $status = 'present';
            $time = randomTimeBetween($studentSettings['day_start_time'], $studentSettings['late_after_time']);
        } elseif ($roll <= 90) {
            $status = 'late';
            $time = randomTimeBetween($studentSettings['late_after_time'], $studentSettings['day_end_time']);
        } elseif ($roll <= 94) {
            $status = 'permission';
            $time = $studentSettings['day_start_time'];
        } elseif ($roll <= 97) {
            $status = 'sick';
            $time = $studentSettings['day_start_time'];
        } else {
            $status = 'absent';
            $time = $studentSettings['day_start_time'];
        }

        $insert->execute(['student_id' => $studentId, 'teacher_id' => null, 'date' => $dateStr, 'type' => 'in', 'time' => $time, 'status' => $status]);
        $inserted++;

        if (in_array($status, ['present', 'late'], true) && random_int(1, 100) <= 85) {
            $outTime = randomTimeBetween($studentSettings['out_start_time'], $studentSettings['out_end_time']);
            $insert->execute(['student_id' => $studentId, 'teacher_id' => null, 'date' => $dateStr, 'type' => 'out', 'time' => $outTime, 'status' => 'present']);
            $inserted++;
        }
    }

    foreach ($teachers as $teacherId) {
        $roll = random_int(1, 100);
        if ($roll <= 88) {
            $status = 'present';
            $time = randomTimeBetween($teacherSettings['day_start_time'], $teacherSettings['late_after_time']);
        } elseif ($roll <= 95) {
            $status = 'late';
            $time = randomTimeBetween($teacherSettings['late_after_time'], $teacherSettings['day_end_time']);
        } else {
            $status = 'official_duty';
            $time = $teacherSettings['day_start_time'];
        }

        $insert->execute(['student_id' => null, 'teacher_id' => $teacherId, 'date' => $dateStr, 'type' => 'in', 'time' => $time, 'status' => $status]);
        $inserted++;

        if (in_array($status, ['present', 'late'], true) && random_int(1, 100) <= 90) {
            $outTime = randomTimeBetween($teacherSettings['out_start_time'], $teacherSettings['out_end_time']);
            $insert->execute(['student_id' => null, 'teacher_id' => $teacherId, 'date' => $dateStr, 'type' => 'out', 'time' => $outTime, 'status' => 'present']);
            $inserted++;
        }
    }
}

echo "Demo attendance generated: {$inserted} rows across the last {$days} days.\n";
