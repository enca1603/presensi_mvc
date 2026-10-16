<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;
use App\Services\FileUploadService;

class SettingsController extends Controller
{
    public function school(Request $request): void
    {
        $school = Database::connection()->query('SELECT * FROM school_settings ORDER BY id LIMIT 1')->fetch();
        $this->view('settings.school', ['title' => 'Pengaturan Sekolah', 'school' => $school]);
    }

    public function updateSchool(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['school_name', 'npsn', 'address', 'phone', 'email', 'principal_name']);
        $validator = $this->validate($data, ['school_name' => 'required|max:150']);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/settings/school');
            return;
        }

        $db = Database::connection();
        $existing = $db->query('SELECT id, logo FROM school_settings ORDER BY id LIMIT 1')->fetch();

        $logoPath = $existing['logo'] ?? null;
        $logoFile = $request->file('logo');
        if ($logoFile) {
            $logoPath = FileUploadService::uploadPhoto($logoFile, 'school');
        }

        if ($existing) {
            $stmt = $db->prepare(
                'UPDATE school_settings SET school_name=:n, npsn=:npsn, address=:a, phone=:p, email=:e, principal_name=:pr, logo=:logo WHERE id=:id'
            );
            $stmt->execute([
                'n' => $data['school_name'], 'npsn' => $data['npsn'] ?: null, 'a' => $data['address'] ?: null,
                'p' => $data['phone'] ?: null, 'e' => $data['email'] ?: null, 'pr' => $data['principal_name'] ?: null,
                'logo' => $logoPath, 'id' => $existing['id'],
            ]);
        } else {
            $stmt = $db->prepare(
                'INSERT INTO school_settings (school_name, npsn, address, phone, email, principal_name, logo) VALUES (:n,:npsn,:a,:p,:e,:pr,:logo)'
            );
            $stmt->execute([
                'n' => $data['school_name'], 'npsn' => $data['npsn'] ?: null, 'a' => $data['address'] ?: null,
                'p' => $data['phone'] ?: null, 'e' => $data['email'] ?: null, 'pr' => $data['principal_name'] ?: null,
                'logo' => $logoPath,
            ]);
        }

        AuditLogService::log('update', 'settings', null, 'Memperbarui profil sekolah');
        Session::flash('success', 'Profil sekolah berhasil disimpan.');
        $this->redirect('/settings/school');
    }

    public function attendanceHours(Request $request): void
    {
        $stmt = Database::connection()->query('SELECT * FROM attendance_settings');
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['type']] = $row;
        }

        $this->view('settings.attendance_hours', ['title' => 'Jam Presensi', 'settings' => $settings]);
    }

    public function updateAttendanceHours(Request $request): void
    {
        $this->requireCsrf();

        $db = Database::connection();
        foreach (['student', 'teacher'] as $type) {
            $prefix = $type . '_';
            $data = [
                'day_start_time' => $request->input($prefix . 'day_start_time'),
                'late_after_time' => $request->input($prefix . 'late_after_time'),
                'day_end_time' => $request->input($prefix . 'day_end_time'),
                'out_enabled' => $request->input($prefix . 'out_enabled') ? 1 : 0,
                'out_start_time' => $request->input($prefix . 'out_start_time'),
                'out_end_time' => $request->input($prefix . 'out_end_time'),
            ];

            $stmt = $db->prepare(
                'UPDATE attendance_settings SET day_start_time=:s, late_after_time=:l, day_end_time=:e,
                 out_enabled=:oe, out_start_time=:os, out_end_time=:oend WHERE type=:type'
            );
            $stmt->execute([
                's' => $data['day_start_time'], 'l' => $data['late_after_time'], 'e' => $data['day_end_time'],
                'oe' => $data['out_enabled'], 'os' => $data['out_start_time'], 'oend' => $data['out_end_time'],
                'type' => $type,
            ]);
        }

        AuditLogService::log('update', 'settings', null, 'Memperbarui jam presensi');
        Session::flash('success', 'Jam presensi berhasil diperbarui.');
        $this->redirect('/settings/attendance-hours');
    }

    public function schoolDays(Request $request): void
    {
        $stmt = Database::connection()->query('SELECT * FROM school_days ORDER BY day_of_week');
        $this->view('settings.school_days', ['title' => 'Hari Sekolah', 'days' => $stmt->fetchAll()]);
    }

    public function updateSchoolDays(Request $request): void
    {
        $this->requireCsrf();

        $activeDays = (array) $request->input('active_days', []);
        $db = Database::connection();
        $stmt = $db->prepare('UPDATE school_days SET is_active = :active WHERE day_of_week = :d');

        for ($d = 0; $d <= 6; $d++) {
            $stmt->execute(['active' => in_array((string) $d, $activeDays, true) ? 1 : 0, 'd' => $d]);
        }

        AuditLogService::log('update', 'settings', null, 'Memperbarui hari sekolah aktif');
        Session::flash('success', 'Hari sekolah berhasil diperbarui.');
        $this->redirect('/settings/school-days');
    }

    public function auditLog(Request $request): void
    {
        $module = $request->query('module');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($module) {
            $where = 'WHERE al.module = :module';
            $params['module'] = $module;
        }

        $db = Database::connection();
        $countStmt = $db->prepare("SELECT COUNT(*) c FROM audit_logs al {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['c'];

        $stmt = $db->prepare(
            "SELECT al.*, u.full_name AS user_name FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             {$where}
             ORDER BY al.id DESC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $modules = $db->query('SELECT DISTINCT module FROM audit_logs ORDER BY module')->fetchAll(\PDO::FETCH_COLUMN);

        $this->view('settings.audit_log', [
            'title' => 'Audit Log',
            'logs' => $stmt->fetchAll(),
            'modules' => $modules,
            'module' => $module,
            'page' => $page,
            'lastPage' => (int) max(1, ceil($total / $perPage)),
        ]);
    }
}
