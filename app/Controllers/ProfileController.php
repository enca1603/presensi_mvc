<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;
use App\Services\QrCodeService;

class ProfileController extends Controller
{
    public function show(Request $request): void
    {
        $user = Auth::user();
        $student = null;
        $teacher = null;
        $qrToken = null;

        $stmt = Database::connection()->prepare('SELECT student_id, teacher_id FROM users WHERE id = :id');
        $stmt->execute(['id' => Auth::id()]);
        $link = $stmt->fetch();

        if (!empty($link['student_id'])) {
            $s = Database::connection()->prepare(
                "SELECT s.*, c.name AS class_name, m.name AS major_name FROM students s
                 LEFT JOIN classes c ON c.id = s.class_id LEFT JOIN majors m ON m.id = s.major_id
                 WHERE s.id = :id"
            );
            $s->execute(['id' => $link['student_id']]);
            $student = $s->fetch() ?: null;
            if ($student) {
                $qrToken = QrCodeService::getActiveToken('student_qr_codes', 'student_id', (int) $student['id']);
            }
        }

        if (!empty($link['teacher_id'])) {
            $t = Database::connection()->prepare('SELECT * FROM teachers WHERE id = :id');
            $t->execute(['id' => $link['teacher_id']]);
            $teacher = $t->fetch() ?: null;
            if ($teacher) {
                $qrToken = QrCodeService::getActiveToken('teacher_qr_codes', 'teacher_id', (int) $teacher['id']);
            }
        }

        $this->view('settings.profile', [
            'title' => 'Profil Saya',
            'user' => $user,
            'student' => $student,
            'teacher' => $teacher,
            'qrToken' => $qrToken,
            'showDefaultPasswordWarning' => \App\Services\SecurityCheckService::isUsingDefaultPassword(),
        ]);
    }

    public function qrImage(Request $request): void
    {
        $stmt = Database::connection()->prepare('SELECT student_id, teacher_id FROM users WHERE id = :id');
        $stmt->execute(['id' => Auth::id()]);
        $link = $stmt->fetch();

        $token = null;
        if (!empty($link['student_id'])) {
            $token = QrCodeService::getActiveToken('student_qr_codes', 'student_id', (int) $link['student_id']);
        } elseif (!empty($link['teacher_id'])) {
            $token = QrCodeService::getActiveToken('teacher_qr_codes', 'teacher_id', (int) $link['teacher_id']);
        }

        if (!$token) {
            http_response_code(404);
            return;
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-store');
        echo QrCodeService::renderPng($token, 320);
    }

    public function changePassword(Request $request): void
    {
        $this->requireCsrf();

        $current = (string) $request->input('current_password');
        $new = (string) $request->input('new_password');
        $confirm = (string) $request->input('new_password_confirmation');

        $stmt = Database::connection()->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute(['id' => Auth::id()]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current, $user['password'])) {
            Session::flash('error', 'Password saat ini tidak sesuai.');
            $this->redirect('/profile');
            return;
        }

        if (strlen($new) < 6) {
            Session::flash('error', 'Password baru minimal 6 karakter.');
            $this->redirect('/profile');
            return;
        }

        if ($new !== $confirm) {
            Session::flash('error', 'Konfirmasi password baru tidak cocok.');
            $this->redirect('/profile');
            return;
        }

        $update = Database::connection()->prepare('UPDATE users SET password = :p WHERE id = :id');
        $update->execute(['p' => password_hash($new, PASSWORD_DEFAULT), 'id' => Auth::id()]);

        AuditLogService::log('change_password', 'auth', (string) Auth::id(), 'Mengganti password sendiri');
        Session::flash('success', 'Password berhasil diganti.');
        $this->redirect('/profile');
    }
}
