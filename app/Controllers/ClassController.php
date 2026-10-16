<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Teacher;
use App\Services\AuditLogService;

class ClassController extends Controller
{
    public function index(Request $request): void
    {
        $stmt = Database::connection()->query(
            "SELECT c.*, m.name AS major_name, ay.name AS academic_year_name, t.full_name AS homeroom_name,
                    (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) AS student_count
             FROM classes c
             LEFT JOIN majors m ON m.id = c.major_id
             LEFT JOIN academic_years ay ON ay.id = c.academic_year_id
             LEFT JOIN teachers t ON t.id = c.homeroom_teacher_id
             ORDER BY c.grade ASC, c.name ASC"
        );

        $this->view('classes.index', ['title' => 'Data Kelas', 'classes' => $stmt->fetchAll()]);
    }

    public function create(Request $request): void
    {
        $this->view('classes.form', [
            'title' => 'Tambah Kelas',
            'class' => null,
            'majors' => Major::all('name'),
            'academicYears' => AcademicYear::all('name'),
            'teachers' => Teacher::where(['status' => 'active']),
        ]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'grade', 'major_id', 'academic_year_id', 'homeroom_teacher_id', 'status']);
        $validator = $this->validate($data, [
            'name' => 'required|max:50',
            'grade' => 'required|max:10',
            'academic_year_id' => 'required',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect('/classes/create');
            return;
        }

        $data['major_id'] = $data['major_id'] ?: null;
        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;
        $data['status'] = $data['status'] ?? 'active';

        $id = ClassRoom::create($data);
        AuditLogService::log('create', 'classes', (string) $id, "Menambahkan kelas {$data['name']}", null, $data);
        Session::flash('success', 'Kelas berhasil ditambahkan.');
        $this->redirect('/classes');
    }

    public function edit(Request $request, string $id): void
    {
        $class = ClassRoom::find((int) $id);
        if (!$class) {
            Session::flash('error', 'Kelas tidak ditemukan.');
            $this->redirect('/classes');
            return;
        }

        $this->view('classes.form', [
            'title' => 'Edit Kelas',
            'class' => $class,
            'majors' => Major::all('name'),
            'academicYears' => AcademicYear::all('name'),
            'teachers' => Teacher::where(['status' => 'active']),
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'grade', 'major_id', 'academic_year_id', 'homeroom_teacher_id', 'status']);
        $validator = $this->validate($data, [
            'name' => 'required|max:50',
            'grade' => 'required|max:10',
            'academic_year_id' => 'required',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect("/classes/{$id}/edit");
            return;
        }

        $data['major_id'] = $data['major_id'] ?: null;
        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;
        $data['status'] = $data['status'] ?? 'active';

        ClassRoom::update((int) $id, $data);
        AuditLogService::log('update', 'classes', $id, "Memperbarui kelas {$data['name']}", null, $data);
        Session::flash('success', 'Data kelas berhasil diperbarui.');
        $this->redirect('/classes');
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $class = ClassRoom::find((int) $id);
        if ($class) {
            ClassRoom::delete((int) $id);
            AuditLogService::log('delete', 'classes', $id, "Menghapus kelas {$class['name']}", $class, null);
            Session::flash('success', 'Kelas berhasil dihapus.');
        }

        $this->redirect('/classes');
    }
}
