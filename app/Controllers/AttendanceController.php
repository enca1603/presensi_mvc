<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\ClassRoom;
use App\Services\AttendanceService;
use App\Services\AuditLogService;

class AttendanceController extends Controller
{
    public function students(Request $request): void
    {
        $date = $request->query('date', date('Y-m-d'));
        $classId = $request->query('class_id');
        $status = $request->query('status');

        $conditions = ['a.attendance_date = :date', 'a.student_id IS NOT NULL'];
        $params = ['date' => $date];

        if ($classId) {
            $conditions[] = 's.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        if ($status) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $status;
        }

        $where = implode(' AND ', $conditions);
        $stmt = Database::connection()->prepare(
            "SELECT a.*, s.full_name, s.nis, c.name AS class_name
             FROM attendances a
             JOIN students s ON s.id = a.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE {$where}
             ORDER BY a.scan_time DESC"
        );
        $stmt->execute($params);

        $this->view('attendance.students', [
            'title' => 'Presensi Siswa',
            'rows' => $stmt->fetchAll(),
            'date' => $date,
            'classId' => $classId,
            'status' => $status,
            'classes' => ClassRoom::all('name'),
        ]);
    }

    public function teachers(Request $request): void
    {
        $date = $request->query('date', date('Y-m-d'));
        $status = $request->query('status');

        $conditions = ['a.attendance_date = :date', 'a.teacher_id IS NOT NULL'];
        $params = ['date' => $date];
        if ($status) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $status;
        }
        $where = implode(' AND ', $conditions);

        $stmt = Database::connection()->prepare(
            "SELECT a.*, t.full_name, t.nip
             FROM attendances a
             JOIN teachers t ON t.id = a.teacher_id
             WHERE {$where}
             ORDER BY a.scan_time DESC"
        );
        $stmt->execute($params);

        $this->view('attendance.teachers', [
            'title' => 'Presensi Guru',
            'rows' => $stmt->fetchAll(),
            'date' => $date,
            'status' => $status,
        ]);
    }

    public function manualForm(Request $request): void
    {
        $stmt = Database::connection()->query('SELECT id, full_name, nis FROM students WHERE status = "active" ORDER BY full_name');
        $students = $stmt->fetchAll();
        $stmt2 = Database::connection()->query('SELECT id, full_name, nip FROM teachers WHERE status = "active" ORDER BY full_name');
        $teachers = $stmt2->fetchAll();

        $this->view('attendance.manual', [
            'title' => 'Presensi Manual',
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    public function manualStore(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['person_type', 'person_id', 'attendance_date', 'type', 'scan_time', 'status', 'notes']);

        $validator = $this->validate($data, [
            'person_type' => 'required|in:student,teacher',
            'person_id' => 'required|integer',
            'attendance_date' => 'required|date',
            'type' => 'required|in:in,out',
            'status' => 'required|in:present,late,permission,sick,absent,official_duty',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/attendance/manual');
            return;
        }

        try {
            \App\Core\Auth::id();
            $id = AttendanceService::recordManual($data, (int) \App\Core\Auth::id());
            Session::flash('success', 'Presensi manual berhasil disimpan.');
            $this->redirect('/attendance/manual');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/attendance/manual');
        }
    }

    public function corrections(Request $request): void
    {
        $date = $request->query('date', date('Y-m-d'));

        $stmt = Database::connection()->prepare(
            "SELECT a.*, COALESCE(s.full_name, t.full_name) AS person_name, IF(a.student_id IS NOT NULL, 'Siswa', 'Guru') AS person_type
             FROM attendances a
             LEFT JOIN students s ON s.id = a.student_id
             LEFT JOIN teachers t ON t.id = a.teacher_id
             WHERE a.attendance_date = :date
             ORDER BY a.scan_time DESC"
        );
        $stmt->execute(['date' => $date]);

        $this->view('attendance.corrections', [
            'title' => 'Koreksi Presensi',
            'rows' => $stmt->fetchAll(),
            'date' => $date,
        ]);
    }

    public function correctionForm(Request $request, string $id): void
    {
        $stmt = Database::connection()->prepare(
            "SELECT a.*, COALESCE(s.full_name, t.full_name) AS person_name
             FROM attendances a
             LEFT JOIN students s ON s.id = a.student_id
             LEFT JOIN teachers t ON t.id = a.teacher_id
             WHERE a.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $attendance = $stmt->fetch();

        if (!$attendance) {
            Session::flash('error', 'Data presensi tidak ditemukan.');
            $this->redirect('/attendance/corrections');
            return;
        }

        $this->view('attendance.correction_form', ['title' => 'Koreksi Presensi', 'attendance' => $attendance]);
    }

    public function correctionStore(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['attendance_date', 'scan_time', 'status', 'notes']);
        $reason = (string) $request->input('reason');

        $validator = $this->validate($data + ['reason' => $reason], [
            'attendance_date' => 'required|date',
            'scan_time' => 'required',
            'status' => 'required|in:present,late,permission,sick,absent,official_duty',
            'reason' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect("/attendance/corrections/{$id}/edit");
            return;
        }

        try {
            AttendanceService::correct((int) $id, $data, $reason, (int) \App\Core\Auth::id());
            Session::flash('success', 'Presensi berhasil dikoreksi.');
            $this->redirect('/attendance/corrections');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect("/attendance/corrections/{$id}/edit");
        }
    }
}
