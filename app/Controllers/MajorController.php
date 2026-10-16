<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Major;
use App\Services\AuditLogService;

class MajorController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('majors.index', ['title' => 'Data Jurusan', 'majors' => Major::all('name')]);
    }

    public function create(Request $request): void
    {
        $this->view('majors.form', ['title' => 'Tambah Jurusan', 'major' => null]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'code', 'status']);
        $validator = $this->validate($data, ['name' => 'required|max:100', 'code' => 'required|max:20']);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect('/majors/create');
            return;
        }

        if (Major::findBy('code', $data['code'])) {
            flash_old($data);
            Session::flash('error', 'Kode jurusan sudah digunakan.');
            $this->redirect('/majors/create');
            return;
        }

        $data['status'] = $data['status'] ?? 'active';
        $id = Major::create($data);
        AuditLogService::log('create', 'majors', (string) $id, "Menambahkan jurusan {$data['name']}", null, $data);
        Session::flash('success', 'Jurusan berhasil ditambahkan.');
        $this->redirect('/majors');
    }

    public function edit(Request $request, string $id): void
    {
        $major = Major::find((int) $id);
        if (!$major) {
            Session::flash('error', 'Jurusan tidak ditemukan.');
            $this->redirect('/majors');
            return;
        }
        $this->view('majors.form', ['title' => 'Edit Jurusan', 'major' => $major]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'code', 'status']);
        $validator = $this->validate($data, ['name' => 'required|max:100', 'code' => 'required|max:20']);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect("/majors/{$id}/edit");
            return;
        }

        $existing = Major::findBy('code', $data['code']);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            flash_old($data);
            Session::flash('error', 'Kode jurusan sudah digunakan jurusan lain.');
            $this->redirect("/majors/{$id}/edit");
            return;
        }

        $data['status'] = $data['status'] ?? 'active';
        Major::update((int) $id, $data);
        AuditLogService::log('update', 'majors', $id, "Memperbarui jurusan {$data['name']}", null, $data);
        Session::flash('success', 'Data jurusan berhasil diperbarui.');
        $this->redirect('/majors');
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $major = Major::find((int) $id);
        if ($major) {
            Major::delete((int) $id);
            AuditLogService::log('delete', 'majors', $id, "Menghapus jurusan {$major['name']}", $major, null);
            Session::flash('success', 'Jurusan berhasil dihapus.');
        }

        $this->redirect('/majors');
    }
}
