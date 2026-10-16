<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;

class RoleController extends Controller
{
    public function index(Request $request): void
    {
        $db = Database::connection();
        $roles = $db->query('SELECT * FROM roles ORDER BY id')->fetchAll();
        $permissions = $db->query('SELECT * FROM permissions ORDER BY module, name')->fetchAll();

        $assigned = $db->query('SELECT role_id, permission_id FROM role_permissions')->fetchAll();
        $map = [];
        foreach ($assigned as $row) {
            $map[$row['role_id']][$row['permission_id']] = true;
        }

        $this->view('settings.roles', [
            'title' => 'Role & Permission',
            'roles' => $roles,
            'permissions' => $permissions,
            'map' => $map,
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $this->requireCsrf();

        $db = Database::connection();
        $role = $db->prepare('SELECT * FROM roles WHERE id = :id');
        $role->execute(['id' => $id]);
        $role = $role->fetch();

        if (!$role) {
            Session::flash('error', 'Role tidak ditemukan.');
            $this->redirect('/settings/roles');
            return;
        }

        if ($role['slug'] === 'super_admin') {
            Session::flash('error', 'Permission Super Admin tidak dapat diubah (selalu penuh).');
            $this->redirect('/settings/roles');
            return;
        }

        $permissionIds = array_map('intval', (array) $request->input('permissions', []));

        $db->beginTransaction();
        try {
            $db->prepare('DELETE FROM role_permissions WHERE role_id = :id')->execute(['id' => $id]);
            $insert = $db->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:r, :p)');
            foreach ($permissionIds as $pid) {
                $insert->execute(['r' => $id, 'p' => $pid]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('error', 'Gagal memperbarui permission.');
            $this->redirect('/settings/roles');
            return;
        }

        AuditLogService::log('update', 'roles', $id, "Memperbarui permission role {$role['name']}");
        Session::flash('success', 'Permission role berhasil diperbarui.');
        $this->redirect('/settings/roles');
    }
}
