<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\Guardian;
use App\Services\AuditLogService;

class GuardianController extends Controller
{
    public function index(Request $request): void
    {
        $search = $request->query('search');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $where = '';
        $params = [];
        if ($search) {
            $where = 'WHERE name LIKE :s1 OR phone LIKE :s2';
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $db = Database::connection();
        $countStmt = $db->prepare("SELECT COUNT(*) c FROM guardians {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['c'];

        $stmt = $db->prepare("SELECT * FROM guardians {$where} ORDER BY name ASC LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $this->view('guardians.index', [
            'title' => 'Data Wali Murid',
            'guardians' => $stmt->fetchAll(),
            'search' => $search,
            'pagination' => ['page' => $page, 'last_page' => (int) max(1, ceil($total / $perPage)), 'total' => $total],
        ]);
    }

    public function create(Request $request): void
    {
        $this->view('guardians.form', ['title' => 'Tambah Wali Murid', 'guardian' => null]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'relationship', 'phone', 'email', 'address', 'status']);
        $validator = $this->validate($data, [
            'name' => 'required|max:150',
            'relationship' => 'required|in:father,mother,guardian',
            'phone' => 'phone',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect('/guardians/create');
            return;
        }

        $id = Guardian::create($data + ['status' => $data['status'] ?? 'active']);
        AuditLogService::log('create', 'guardians', (string) $id, "Menambahkan wali murid {$data['name']}", null, $data);
        Session::flash('success', 'Wali murid berhasil ditambahkan.');
        $this->redirect('/guardians');
    }

    public function edit(Request $request, string $id): void
    {
        $guardian = Guardian::find((int) $id);
        if (!$guardian) {
            Session::flash('error', 'Wali murid tidak ditemukan.');
            $this->redirect('/guardians');
            return;
        }
        $this->view('guardians.form', ['title' => 'Edit Wali Murid', 'guardian' => $guardian]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['name', 'relationship', 'phone', 'email', 'address', 'status']);
        $validator = $this->validate($data, [
            'name' => 'required|max:150',
            'relationship' => 'required|in:father,mother,guardian',
            'phone' => 'phone',
        ]);

        if ($validator->fails()) {
            flash_old($data);
            Session::flash('error', $validator->firstError());
            $this->redirect("/guardians/{$id}/edit");
            return;
        }

        Guardian::update((int) $id, $data);
        AuditLogService::log('update', 'guardians', $id, "Memperbarui wali murid {$data['name']}", null, $data);
        Session::flash('success', 'Data wali murid berhasil diperbarui.');
        $this->redirect('/guardians');
    }

    public function destroy(Request $request, string $id): void
    {
        $this->requireCsrf();

        $guardian = Guardian::find((int) $id);
        if ($guardian) {
            Guardian::delete((int) $id);
            AuditLogService::log('delete', 'guardians', $id, "Menghapus wali murid {$guardian['name']}", $guardian, null);
            Session::flash('success', 'Wali murid berhasil dihapus.');
        }

        $this->redirect('/guardians');
    }
}
