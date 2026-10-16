<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\AcademicYear;
use App\Services\AuditLogService;

class AcademicYearController extends Controller
{
    public function index(Request $request): void
    {
        $years = AcademicYear::all('start_date', 'DESC');
        $this->view('academic_years.index', ['title' => 'Tahun Ajaran', 'years' => $years]);
    }

    public function create(Request $request): void
    {
        $this->view('academic_years.form', ['title' => 'Tambah Tahun Ajaran', 'year' => null]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'start_date', 'end_date', 'is_active']);
        $validator = $this->validate($data, [
            'name' => 'required|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect('/academic-years/create');
            return;
        }

        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($isActive) {
            Database::connection()->exec('UPDATE academic_years SET is_active = 0');
        }

        $id = AcademicYear::create(['name' => $data['name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'is_active' => $isActive]);
        AuditLogService::log('create', 'academic_years', (string) $id, "Menambahkan tahun ajaran {$data['name']}", null, $data);
        Session::flash('success', 'Tahun ajaran berhasil ditambahkan.');
        $this->redirect('/academic-years');
    }

    public function edit(Request $request, string $id): void
    {
        $year = AcademicYear::find((int) $id);
        if (!$year) {
            Session::flash('error', 'Tahun ajaran tidak ditemukan.');
            $this->redirect('/academic-years');
            return;
        }
        $this->view('academic_years.form', ['title' => 'Edit Tahun Ajaran', 'year' => $year]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'start_date', 'end_date', 'is_active']);
        $validator = $this->validate($data, [
            'name' => 'required|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect("/academic-years/{$id}/edit");
            return;
        }

        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($isActive) {
            Database::connection()->exec('UPDATE academic_years SET is_active = 0');
        }

        AcademicYear::update((int) $id, ['name' => $data['name'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'is_active' => $isActive]);
        AuditLogService::log('update', 'academic_years', $id, "Memperbarui tahun ajaran {$data['name']}", null, $data);
        Session::flash('success', 'Tahun ajaran berhasil diperbarui.');
        $this->redirect('/academic-years');
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $year = AcademicYear::find((int) $id);
        if ($year) {
            try {
                AcademicYear::delete((int) $id);
                AuditLogService::log('delete', 'academic_years', $id, "Menghapus tahun ajaran {$year['name']}", $year, null);
                Session::flash('success', 'Tahun ajaran berhasil dihapus.');
            } catch (\Throwable $e) {
                Session::flash('error', 'Tahun ajaran tidak dapat dihapus karena masih digunakan oleh data kelas.');
            }
        }

        $this->redirect('/academic-years');
    }
}
