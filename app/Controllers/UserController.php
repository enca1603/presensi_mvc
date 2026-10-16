<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\Role;
use App\Services\AuditLogService;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $stmt = Database::connection()->query(
            'SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.full_name'
        );
        $this->view('settings.users.index', ['title' => 'Manajemen User', 'users' => $stmt->fetchAll()]);
    }

    public function create(Request $request): void
    {
        $this->view('settings.users.form', ['title' => 'Tambah User', 'user' => null, 'roles' => Role::all('name')]);
    }

    public function store(Request $request): void
    {
        $this->requireCsrf();

        $data = $request->only(['role_id', 'username', 'email', 'password', 'full_name', 'status']);
        $validator = $this->validate($data, [
            'role_id' => 'required|integer',
            'username' => 'required|max:100',
            'full_name' => 'required|max:150',
            'password' => 'required|min:6',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/settings/users/create');
            return;
        }

        $db = Database::connection();
        $exists = $db->prepare('SELECT id FROM users WHERE username = :u');
        $exists->execute(['u' => $data['username']]);
        if ($exists->fetch()) {
            Session::flash('error', 'Username sudah digunakan.');
            $this->redirect('/settings/users/create');
            return;
        }

        $stmt = $db->prepare(
            'INSERT INTO users (role_id, username, email, password, full_name, status) VALUES (:r, :u, :e, :p, :f, :s)'
        );
        $stmt->execute([
            'r' => $data['role_id'], 'u' => $data['username'], 'e' => $data['email'] ?: null,
            'p' => password_hash($data['password'], PASSWORD_DEFAULT), 'f' => $data['full_name'], 's' => $data['status'] ?? 'active',
        ]);
        $id = (int) $db->lastInsertId();

        AuditLogService::log('create', 'users', (string) $id, "Menambahkan user {$data['username']}");
        Session::flash('success', 'User berhasil ditambahkan.');
        $this->redirect('/settings/users');
    }

    public function edit(Request $request, string $id): void
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            Session::flash('error', 'User tidak ditemukan.');
            $this->redirect('/settings/users');
            return;
        }

        $this->view('settings.users.form', ['title' => 'Edit User', 'user' => $user, 'roles' => Role::all('name')]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $data = $request->only(['role_id', 'username', 'email', 'password', 'full_name', 'status']);
        $validator = $this->validate($data, [
            'role_id' => 'required|integer',
            'username' => 'required|max:100',
            'full_name' => 'required|max:150',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect("/settings/users/{$id}/edit");
            return;
        }

        $db = Database::connection();
        $existing = $db->prepare('SELECT id FROM users WHERE username = :u AND id != :id');
        $existing->execute(['u' => $data['username'], 'id' => $id]);
        if ($existing->fetch()) {
            Session::flash('error', 'Username sudah digunakan user lain.');
            $this->redirect("/settings/users/{$id}/edit");
            return;
        }

        if (!empty($data['password'])) {
            $stmt = $db->prepare(
                'UPDATE users SET role_id=:r, username=:u, email=:e, password=:p, full_name=:f, status=:s WHERE id=:id'
            );
            $stmt->execute([
                'r' => $data['role_id'], 'u' => $data['username'], 'e' => $data['email'] ?: null,
                'p' => password_hash($data['password'], PASSWORD_DEFAULT), 'f' => $data['full_name'],
                's' => $data['status'] ?? 'active', 'id' => $id,
            ]);
        } else {
            $stmt = $db->prepare(
                'UPDATE users SET role_id=:r, username=:u, email=:e, full_name=:f, status=:s WHERE id=:id'
            );
            $stmt->execute([
                'r' => $data['role_id'], 'u' => $data['username'], 'e' => $data['email'] ?: null,
                'f' => $data['full_name'], 's' => $data['status'] ?? 'active', 'id' => $id,
            ]);
        }

        AuditLogService::log('update', 'users', $id, "Memperbarui user {$data['username']}");
        Session::flash('success', 'User berhasil diperbarui.');
        $this->redirect('/settings/users');
    }
}
