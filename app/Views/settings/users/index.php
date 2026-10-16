<div class="card">
    <div class="card-header">
        <h3>Manajemen User</h3>
        <a href="/settings/users/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah User</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Username</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email'] ?: '-') ?></td>
                    <td><span class="badge badge-blue"><?= e($u['role_name']) ?></span></td>
                    <td><span class="badge <?= $u['status'] === 'active' ? 'badge-green' : 'badge-red' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
                    <td class="text-sm text-muted"><?= $u['last_login_at'] ? format_date($u['last_login_at'], 'd/m/Y H:i') : 'Belum pernah' ?></td>
                    <td><a href="/settings/users/<?= (int) $u['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
