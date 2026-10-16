<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Services\ReportExportService;
use App\Services\ReportService;

class ReportController extends Controller
{
    private const STATUS_LABEL = [
        'present' => 'Hadir', 'late' => 'Terlambat', 'permission' => 'Izin',
        'sick' => 'Sakit', 'absent' => 'Alpa', 'official_duty' => 'Dinas',
    ];

    // -----------------------------------------------------------------
    // Daily
    // -----------------------------------------------------------------
    public function daily(Request $request): void
    {
        $date = $request->query('date', date('Y-m-d'));
        $classId = $request->query('class_id') ?: null;
        $majorId = $request->query('major_id') ?: null;

        $rows = ReportService::daily($date, $classId ? (int) $classId : null, $majorId ? (int) $majorId : null);

        $this->view('reports.daily', [
            'title' => 'Rekap Harian',
            'rows' => $rows,
            'date' => $date,
            'classId' => $classId,
            'majorId' => $majorId,
            'classes' => ClassRoom::all('name'),
            'majors' => Major::all('name'),
        ]);
    }

    public function dailyExport(Request $request): void
    {
        $date = $request->query('date', date('Y-m-d'));
        $classId = $request->query('class_id') ?: null;
        $majorId = $request->query('major_id') ?: null;
        $rows = ReportService::daily($date, $classId ? (int) $classId : null, $majorId ? (int) $majorId : null);

        $columns = ['NIS', 'Nama', 'Kelas', 'Status', 'Jam Masuk', 'Jam Pulang'];
        $tableRows = array_map(fn($r) => [
            $r['nis'], $r['full_name'], $r['class_name'] ?? '-',
            self::STATUS_LABEL[$r['status_final']] ?? 'Belum Hadir',
            $r['time_in'] ?? '-', $r['time_out'] ?? '-',
        ], $rows);

        $period = format_date_id($date);
        $this->export($request, 'rekap-harian-' . $date, 'Rekap Presensi Harian', $period, $columns, $tableRows);
    }

    // -----------------------------------------------------------------
    // Monthly
    // -----------------------------------------------------------------
    public function monthly(Request $request): void
    {
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $classId = $request->query('class_id') ?: null;

        $rows = ReportService::monthly($year, $month, $classId ? (int) $classId : null);

        $this->view('reports.monthly', [
            'title' => 'Rekap Bulanan',
            'rows' => $rows,
            'year' => $year,
            'month' => $month,
            'classId' => $classId,
            'classes' => ClassRoom::all('name'),
        ]);
    }

    public function monthlyExport(Request $request): void
    {
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $classId = $request->query('class_id') ?: null;
        $rows = ReportService::monthly($year, $month, $classId ? (int) $classId : null);

        $columns = ['NIS', 'Nama', 'Kelas', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'];
        $tableRows = array_map(fn($r) => [
            $r['nis'], $r['full_name'], $r['class_name'] ?? '-',
            $r['present_count'], $r['late_count'], $r['permission_count'], $r['sick_count'], $r['absent_count'],
        ], $rows);

        $period = date('F Y', mktime(0, 0, 0, $month, 1, $year));
        $this->export($request, "rekap-bulanan-{$year}-{$month}", 'Rekap Presensi Bulanan', $period, $columns, $tableRows);
    }

    // -----------------------------------------------------------------
    // Students
    // -----------------------------------------------------------------
    public function students(Request $request): void
    {
        $search = $request->query('search');
        $where = '';
        $params = [];
        if ($search) {
            $where = 'WHERE full_name LIKE :s OR nis LIKE :s';
            $params['s'] = "%{$search}%";
        }
        $stmt = Database::connection()->prepare("SELECT id, nis, full_name FROM students {$where} ORDER BY full_name LIMIT 100");
        $stmt->execute($params);

        $this->view('reports.students_index', ['title' => 'Rekap Siswa', 'students' => $stmt->fetchAll(), 'search' => $search]);
    }

    public function studentDetail(Request $request, string $id): void
    {
        $start = $request->query('start', date('Y-m-01'));
        $end = $request->query('end', date('Y-m-d'));

        $data = ReportService::student((int) $id, $start, $end);
        if (!$data['student']) {
            Session::flash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/reports/students');
            return;
        }

        $this->view('reports.student_detail', array_merge($data, [
            'title' => 'Rekap Siswa - ' . $data['student']['full_name'],
            'start' => $start,
            'end' => $end,
        ]));
    }

    public function studentExport(Request $request, string $id): void
    {
        $start = $request->query('start', date('Y-m-01'));
        $end = $request->query('end', date('Y-m-d'));
        $data = ReportService::student((int) $id, $start, $end);

        $columns = ['Tanggal', 'Tipe', 'Jam', 'Status', 'Metode'];
        $tableRows = array_map(fn($h) => [
            format_date($h['attendance_date']), $h['type'] === 'in' ? 'Masuk' : 'Pulang',
            $h['scan_time'], self::STATUS_LABEL[$h['status']] ?? $h['status'], $h['method'] === 'qr' ? 'QR' : 'Manual',
        ], $data['history']);

        $period = format_date($start) . ' s/d ' . format_date($end) . ' — ' . $data['student']['full_name'] . ' (' . $data['student']['nis'] . ')';
        $this->export($request, 'rekap-siswa-' . $data['student']['nis'], 'Rekap Presensi Siswa', $period, $columns, $tableRows);
    }

    // -----------------------------------------------------------------
    // Teachers
    // -----------------------------------------------------------------
    public function teachers(Request $request): void
    {
        $search = $request->query('search');
        $where = '';
        $params = [];
        if ($search) {
            $where = 'WHERE full_name LIKE :s OR nip LIKE :s';
            $params['s'] = "%{$search}%";
        }
        $stmt = Database::connection()->prepare("SELECT id, nip, full_name FROM teachers {$where} ORDER BY full_name LIMIT 100");
        $stmt->execute($params);

        $this->view('reports.teachers_index', ['title' => 'Rekap Guru', 'teachers' => $stmt->fetchAll(), 'search' => $search]);
    }

    public function teacherDetail(Request $request, string $id): void
    {
        $start = $request->query('start', date('Y-m-01'));
        $end = $request->query('end', date('Y-m-d'));

        $data = ReportService::teacher((int) $id, $start, $end);
        if (!$data['teacher']) {
            Session::flash('error', 'Guru tidak ditemukan.');
            $this->redirect('/reports/teachers');
            return;
        }

        $this->view('reports.teacher_detail', array_merge($data, [
            'title' => 'Rekap Guru - ' . $data['teacher']['full_name'],
            'start' => $start,
            'end' => $end,
        ]));
    }

    public function teacherExport(Request $request, string $id): void
    {
        $start = $request->query('start', date('Y-m-01'));
        $end = $request->query('end', date('Y-m-d'));
        $data = ReportService::teacher((int) $id, $start, $end);

        $columns = ['Tanggal', 'Tipe', 'Jam', 'Status', 'Metode'];
        $tableRows = array_map(fn($h) => [
            format_date($h['attendance_date']), $h['type'] === 'in' ? 'Masuk' : 'Pulang',
            $h['scan_time'], self::STATUS_LABEL[$h['status']] ?? $h['status'], $h['method'] === 'qr' ? 'QR' : 'Manual',
        ], $data['history']);

        $period = format_date($start) . ' s/d ' . format_date($end) . ' — ' . $data['teacher']['full_name'];
        $this->export($request, 'rekap-guru-' . ($data['teacher']['nip'] ?: $id), 'Rekap Presensi Guru', $period, $columns, $tableRows);
    }

    // -----------------------------------------------------------------
    // Classes
    // -----------------------------------------------------------------
    public function classes(Request $request): void
    {
        $this->view('reports.classes_index', ['title' => 'Rekap Kelas', 'classes' => ClassRoom::all('name')]);
    }

    public function classDetail(Request $request, string $id): void
    {
        $start = $request->query('start', date('Y-m-01'));
        $end = $request->query('end', date('Y-m-d'));

        $data = ReportService::classReport((int) $id, $start, $end);
        if (!$data['class']) {
            Session::flash('error', 'Kelas tidak ditemukan.');
            $this->redirect('/reports/classes');
            return;
        }

        $this->view('reports.class_detail', array_merge($data, [
            'title' => 'Rekap Kelas - ' . $data['class']['name'],
            'start' => $start,
            'end' => $end,
        ]));
    }

    public function classExport(Request $request, string $id): void
    {
        $start = $request->query('start', date('Y-m-01'));
        $end = $request->query('end', date('Y-m-d'));
        $data = ReportService::classReport((int) $id, $start, $end);

        $columns = ['NIS', 'Nama', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'];
        $tableRows = array_map(fn($s) => [
            $s['nis'], $s['full_name'], $s['present_count'], $s['late_count'], $s['permission_count'], $s['sick_count'], $s['absent_count'],
        ], $data['students']);

        $period = format_date($start) . ' s/d ' . format_date($end) . ' — Kelas ' . $data['class']['name'];
        $this->export($request, 'rekap-kelas-' . $data['class']['name'], 'Rekap Presensi Kelas', $period, $columns, $tableRows);
    }

    // -----------------------------------------------------------------
    // Shared export helper
    // -----------------------------------------------------------------
    private function export(Request $request, string $filename, string $title, string $period, array $columns, array $rows): void
    {
        $format = $request->query('format', 'excel');
        $filename = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $filename);

        if ($format === 'pdf') {
            $html = ReportExportService::buildPdfHtml($title, $period, $columns, $rows);
            ReportExportService::pdf($filename, $html);
            return;
        }

        ReportExportService::excel($filename, $title, $period, $columns, $rows);
    }
}
