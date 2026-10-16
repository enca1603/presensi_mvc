<div class="card">
    <div class="card-header"><h3>Role & Permission</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Super Admin selalu memiliki akses penuh dan tidak dapat diubah. Atur permission untuk role lain di bawah ini.</p>

    <?php foreach ($roles as $role): ?>
        <div class="card" style="box-shadow:none; border-style:dashed; margin-bottom:16px;">
            <h4 style="margin-bottom:12px;"><?= e($role['name']) ?></h4>
            <?php if ($role['slug'] === 'super_admin'): ?>
                <p class="text-sm text-muted">Akses penuh ke seluruh sistem (tidak dapat diubah).</p>
            <?php else: ?>
                <form method="POST" action="/settings/roles/<?= (int) $role['id'] ?>/update">
                    <?= csrf_field() ?>
                    <div class="flex gap-3" style="flex-wrap:wrap; margin-bottom:14px;">
                        <?php foreach ($permissions as $perm): ?>
                            <label class="badge <?= isset($map[$role['id']][$perm['id']]) ? 'badge-green' : 'badge-gray' ?>" style="cursor:pointer;">
                                <input type="checkbox" name="permissions[]" value="<?= (int) $perm['id'] ?>" style="width:auto; margin-right:4px;" <?= isset($map[$role['id']][$perm['id']]) ? 'checked' : '' ?>>
                                <?= e($perm['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Permission <?= e($role['name']) ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
