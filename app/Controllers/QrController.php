<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;
use App\Services\QrCodeService;

class QrController extends Controller
{
    public function students(Request $request): void
    {
        $search = $request->query('search');
        $where = '';
        $params = [];
        if ($search) {
            $where = 'WHERE s.full_name LIKE :s1 OR s.nis LIKE :s2';
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $stmt = Database::connection()->prepare(
            "SELECT s.id, s.nis, s.full_name, s.photo, c.name AS class_name,
                    q.token, q.status AS qr_status, q.generated_at
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN student_qr_codes q ON q.student_id = s.id AND q.status = 'active'
             {$where}
             ORDER BY s.full_name ASC
             LIMIT 200"
        );
        $stmt->execute($params);

        $this->view('qr.students', [
            'title' => 'QR Code Siswa',
            'rows' => $stmt->fetchAll(),
            'search' => $search,
        ]);
    }

    public function teachers(Request $request): void
    {
        $search = $request->query('search');
        $where = '';
        $params = [];
        if ($search) {
            $where = 'WHERE t.full_name LIKE :s1 OR t.nip LIKE :s2';
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $stmt = Database::connection()->prepare(
            "SELECT t.id, t.nip, t.full_name, t.photo, t.position,
                    q.token, q.status AS qr_status, q.generated_at
             FROM teachers t
             LEFT JOIN teacher_qr_codes q ON q.teacher_id = t.id AND q.status = 'active'
             {$where}
             ORDER BY t.full_name ASC
             LIMIT 200"
        );
        $stmt->execute($params);

        $this->view('qr.teachers', [
            'title' => 'QR Code Guru',
            'rows' => $stmt->fetchAll(),
            'search' => $search,
        ]);
    }

    public function regenerateStudent(Request $request, string $id): void
    {
        $this->requireCsrf();
        QrCodeService::generateForStudent((int) $id);
        AuditLogService::log('regenerate_qr', 'students', $id, 'Regenerate QR siswa melalui halaman QR Code');
        Session::flash('success', 'QR Code siswa berhasil dibuat ulang.');
        $this->redirect('/qr/students');
    }

    public function regenerateTeacher(Request $request, string $id): void
    {
        $this->requireCsrf();
        QrCodeService::generateForTeacher((int) $id);
        AuditLogService::log('regenerate_qr', 'teachers', $id, 'Regenerate QR guru melalui halaman QR Code');
        Session::flash('success', 'QR Code guru berhasil dibuat ulang.');
        $this->redirect('/qr/teachers');
    }

    public function printStudent(Request $request, string $id): void
    {
        $stmt = Database::connection()->prepare(
            "SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON c.id = s.class_id WHERE s.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            Session::flash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/qr/students');
            return;
        }

        $token = QrCodeService::getActiveToken('student_qr_codes', 'student_id', (int) $id);

        $this->view('qr.print', [
            'title' => 'Cetak QR',
            'items' => [['name' => $student['full_name'], 'code' => $student['nis'], 'sub' => $student['class_name'] ?? '-', 'image' => "/students/{$id}/qr-image"]],
        ], null);
    }

    public function printTeacher(Request $request, string $id): void
    {
        $stmt = Database::connection()->prepare('SELECT * FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();

        if (!$teacher) {
            Session::flash('error', 'Guru tidak ditemukan.');
            $this->redirect('/qr/teachers');
            return;
        }

        $this->view('qr.print', [
            'title' => 'Cetak QR',
            'items' => [['name' => $teacher['full_name'], 'code' => $teacher['nip'] ?: '-', 'sub' => $teacher['position'] ?? '-', 'image' => "/teachers/{$id}/qr-image"]],
        ], null);
    }

    public function printBulkStudents(Request $request): void
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));
        if (empty($ids)) {
            Session::flash('error', 'Pilih minimal satu siswa untuk dicetak.');
            $this->redirect('/qr/students');
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT s.id, s.full_name, s.nis, c.name AS class_name FROM students s
             LEFT JOIN classes c ON c.id = s.class_id WHERE s.id IN ({$placeholders})"
        );
        $stmt->execute($ids);

        $items = array_map(fn($s) => [
            'name' => $s['full_name'],
            'code' => $s['nis'],
            'sub' => $s['class_name'] ?? '-',
            'image' => "/students/{$s['id']}/qr-image",
        ], $stmt->fetchAll());

        $this->view('qr.print', ['title' => 'Cetak QR', 'items' => $items], null);
    }
}
