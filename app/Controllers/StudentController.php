<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Guardian;
use App\Models\Major;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\QrCodeService;
use App\Services\StudentService;

class StudentController extends Controller
{
    public function index(Request $request): void
    {
        $filters = [
            'search' => $request->query('search'),
            'class_id' => $request->query('class_id'),
            'major_id' => $request->query('major_id'),
            'status' => $request->query('status'),
        ];

        $page = (int) $request->query('page', 1);
        $result = StudentService::paginate($filters, $page);

        $this->view('students.index', [
            'title' => 'Data Siswa',
            'students' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'classes' => ClassRoom::all('name'),
            'majors' => Major::all('name'),
        ]);
    }

    public function create(Request $request): void
    {
        $this->view('students.form', [
            'title' => 'Tambah Siswa',
            'student' => null,
            'classes' => ClassRoom::all('name'),
            'majors' => Major::all('name'),
            'academicYears' => AcademicYear::all('name'),
            'guardians' => Guardian::all('name'),
        ]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only([
            'nis', 'nisn', 'full_name', 'nickname', 'gender', 'birth_place', 'birth_date',
            'address', 'phone', 'email', 'class_id', 'major_id', 'academic_year_id',
            'guardian_id', 'guardian_name', 'guardian_relationship', 'guardian_phone',
            'guardian_email', 'guardian_address', 'entry_year', 'status',
        ]);

        $validator = $this->validate($data, [
            'nis' => 'required|max:30',
            'nisn' => 'required|max:30',
            'full_name' => 'required|max:150',
            'gender' => 'required|in:L,P',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect('/students/create');
            return;
        }

        try {
            $photo = $request->file('photo');
            $studentId = StudentService::create($data, $photo);
            AuditLogService::log('create', 'students', (string) $studentId, "Menambahkan siswa {$data['full_name']}", null, $data);
            Session::flash('success', 'Siswa berhasil ditambahkan dan QR Code otomatis dibuat.');
            $this->redirect('/students');
        } catch (\Throwable $e) {
            flash_old($data);
            Session::flash('error', $e->getMessage());
            $this->redirect('/students/create');
        }
    }

    public function show(Request $request, string $id): void
    {
        $student = StudentService::findWithRelations((int) $id);
        if (!$student) {
            Session::flash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/students');
            return;
        }

        $token = QrCodeService::getActiveToken('student_qr_codes', 'student_id', (int) $id);

        $this->view('students.show', [
            'title' => 'Detail Siswa',
            'student' => $student,
            'token' => $token,
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $student = Student::find((int) $id);
        if (!$student) {
            Session::flash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/students');
            return;
        }

        $this->view('students.form', [
            'title' => 'Edit Siswa',
            'student' => $student,
            'classes' => ClassRoom::all('name'),
            'majors' => Major::all('name'),
            'academicYears' => AcademicYear::all('name'),
            'guardians' => Guardian::all('name'),
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only([
            'nis', 'nisn', 'full_name', 'nickname', 'gender', 'birth_place', 'birth_date',
            'address', 'phone', 'email', 'class_id', 'major_id', 'academic_year_id',
            'guardian_id', 'entry_year', 'status',
        ]);

        $validator = $this->validate($data, [
            'nis' => 'required|max:30',
            'nisn' => 'required|max:30',
            'full_name' => 'required|max:150',
            'gender' => 'required|in:L,P',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect("/students/{$id}/edit");
            return;
        }

        try {
            $photo = $request->file('photo');
            StudentService::update((int) $id, $data, $photo);
            AuditLogService::log('update', 'students', $id, "Memperbarui data siswa {$data['full_name']}", null, $data);
            Session::flash('success', 'Data siswa berhasil diperbarui.');
            $this->redirect('/students');
        } catch (\Throwable $e) {
            flash_old($data);
            Session::flash('error', $e->getMessage());
            $this->redirect("/students/{$id}/edit");
        }
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $student = Student::find((int) $id);
        if ($student) {
            StudentService::delete((int) $id);
            AuditLogService::log('delete', 'students', $id, "Menghapus siswa {$student['full_name']}", $student, null);
            Session::flash('success', 'Siswa berhasil dihapus.');
        }

        $this->redirect('/students');
    }

    public function qrImage(Request $request, string $id): void
    {
        $token = QrCodeService::getActiveToken('student_qr_codes', 'student_id', (int) $id);
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

        $student = Student::find((int) $id);
        if (!$student) {
            Session::flash('error', 'Siswa tidak ditemukan.');
            $this->redirect('/students');
            return;
        }

        QrCodeService::generateForStudent((int) $id);
        AuditLogService::log('regenerate_qr', 'students', $id, "Regenerate QR siswa {$student['full_name']}");
        Session::flash('success', 'QR Code baru berhasil dibuat, QR lama telah dinonaktifkan.');
        $this->redirect("/students/{$id}");
    }
}
