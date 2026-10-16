<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class KioskApiController extends Controller
{
    public function stats(Request $request): void
    {
        $today = date('Y-m-d');
        $db = Database::connection();

        $totalActive = (int) $db->query("SELECT COUNT(*) c FROM students WHERE status = 'active'")->fetch()['c'];

        $stmt = $db->prepare(
            "SELECT
                SUM(CASE WHEN type = 'in' AND status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN type = 'in' AND status = 'late' THEN 1 ELSE 0 END) AS late_count,
                COUNT(DISTINCT CASE WHEN type = 'out' THEN student_id END) AS gone_home_count,
                COUNT(DISTINCT CASE WHEN type = 'in' THEN student_id END) AS checked_in_count
             FROM attendances
             WHERE attendance_date = :date AND student_id IS NOT NULL"
        );
        $stmt->execute(['date' => $today]);
        $row = $stmt->fetch();

        $hadir = (int) ($row['present_count'] ?? 0) + (int) ($row['late_count'] ?? 0);
        $checkedIn = (int) ($row['checked_in_count'] ?? 0);

        Response::success([
            'hadir' => $hadir,
            'terlambat' => (int) ($row['late_count'] ?? 0),
            'sudah_pulang' => (int) ($row['gone_home_count'] ?? 0),
            'belum_hadir' => max(0, $totalActive - $checkedIn),
            'total_siswa' => $totalActive,
        ], 'Statistik presensi hari ini');
    }
}
