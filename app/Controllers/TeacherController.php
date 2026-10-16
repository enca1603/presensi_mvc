<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Teacher;
use App\Services\AuditLogService;
use App\Services\QrCodeService;
use App\Services\TeacherService;

class TeacherController extends Controller
{
    public function index(Request $request): void
    {
        $filters = ['search' => $request->query('search'), 'status' => $request->query('status')];
        $page = (int) $request->query('page', 1);
        $result = TeacherService::paginate($filters, $page);

        $this->view('teachers.index', [
            'title' => 'Data Guru',
            'teachers' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): void
    {
        $this->view('teachers.form', ['title' => 'Tambah Guru', 'teacher' => null]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['nip', 'nuptk', 'full_name', 'gender', 'birth_place', 'birth_date', 'address', 'phone', 'email', 'position', 'subject', 'status']);
        $validator = $this->validate($data, ['full_name' => 'required|max:150', 'gender' => 'required|in:L,P']);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect('/teachers/create');
            return;
        }

        try {
            $teacherId = TeacherService::create($data, $request->file('photo'));
            AuditLogService::log('create', 'teachers', (string) $teacherId, "Menambahkan guru {$data['full_name']}", null, $data);
            Session::flash('success', 'Guru berhasil ditambahkan dan QR Code otomatis dibuat.');
            $this->redirect('/teachers');
        } catch (\Throwable $e) {
            flash_old($data);
            Session::flash('error', $e->getMessage());
            $this->redirect('/teachers/create');
        }
    }

    public function show(Request $request, string $id): void
    {
        $teacher = Teacher::find((int) $id);
        if (!$teacher) {
            Session::flash('error', 'Guru tidak ditemukan.');
            $this->redirect('/teachers');
            return;
        }

        $token = QrCodeService::getActiveToken('teacher_qr_codes', 'teacher_id', (int) $id);
        $this->view('teachers.show', ['title' => 'Detail Guru', 'teacher' => $teacher, 'token' => $token]);
    }

    public function edit(Request $request, string $id): void
    {
        $teacher = Teacher::find((int) $id);
        if (!$teacher) {
            Session::flash('error', 'Guru tidak ditemukan.');
            $this->redirect('/teachers');
            return;
        }

        $this->view('teachers.form', ['title' => 'Edit Guru', 'teacher' => $teacher]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['nip', 'nuptk', 'full_name', 'gender', 'birth_place', 'birth_date', 'address', 'phone', 'email', 'position', 'subject', 'status']);
        $validator = $this->validate($data, ['full_name' => 'required|max:150', 'gender' => 'required|in:L,P']);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect("/teachers/{$id}/edit");
            return;
        }

        try {
            TeacherService::update((int) $id, $data, $request->file('photo'));
            AuditLogService::log('update', 'teachers', $id, "Memperbarui data guru {$data['full_name']}", null, $data);
            Session::flash('success', 'Data guru berhasil diperbarui.');
            $this->redirect('/teachers');
        } catch (\Throwable $e) {
            flash_old($data);
            Session::flash('error', $e->getMessage());
            $this->redirect("/teachers/{$id}/edit");
        }
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $teacher = Teacher::find((int) $id);
        if ($teacher) {
            TeacherService::delete((int) $id);
            AuditLogService::log('delete', 'teachers', $id, "Menghapus guru {$teacher['full_name']}", $teacher, null);
            Session::flash('success', 'Guru berhasil dihapus.');
        }

        $this->redirect('/teachers');
    }

    public function qrImage(Request $request, string $id): void
    {
        $token = QrCodeService::getActiveToken('teacher_qr_codes', 'teacher_id', (int) $id);
        if (!$token) {
            http_response_code(404);
            return;
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-store');
        echo QrCodeService::renderPng($token, 320);
    }

    public function regenerateQr(Request $request, string $id): void
    {
        $this->requireCsrf();

        $teacher = Teacher::find((int) $id);
        if (!$teacher) {
            Session::flash('error', 'Guru tidak ditemukan.');
            $this->redirect('/teachers');
            return;
        }

        QrCodeService::generateForTeacher((int) $id);
        AuditLogService::log('regenerate_qr', 'teachers', $id, "Regenerate QR guru {$teacher['full_name']}");
        Session::flash('success', 'QR Code baru berhasil dibuat.');
        $this->redirect("/teachers/{$id}");
    }
}
