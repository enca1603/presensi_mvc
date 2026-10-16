<?php $isEdit = !empty($user); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit User' : 'Tambah User' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/settings/users/' . $user['id'] . '/update' : '/settings/users/store' ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-control" required value="<?= e($isEdit ? $user['username'] : old('username')) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($isEdit ? $user['email'] : old('email')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="full_name">Nama Lengkap *</label>
            <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= e($isEdit ? $user['full_name'] : old('full_name')) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role_id">Role *</label>
                <select id="role_id" name="role_id" class="form-control" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= (int) $r['id'] ?>" <?= (string) ($isEdit ? $user['role_id'] : old('role_id')) === (string) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" <?= ($isEdit ? $user['status'] : old('status', 'active')) === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= ($isEdit ? $user['status'] : old('status')) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="password"><?= $isEdit ? 'Password Baru (opsional)' : 'Password *' ?></label>
            <input type="password" id="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?> autocomplete="new-password">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/settings/users" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
