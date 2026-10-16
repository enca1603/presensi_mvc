<div class="card">
    <div class="card-header"><h3>Profil Saya</h3></div>

    <div class="flex gap-3" style="align-items:flex-start; flex-wrap:wrap;">
        <img class="avatar-lg" src="<?= e(($student['photo'] ?? $teacher['photo'] ?? '')) ?>" onerror="this.src='https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($user['full_name']) ?>'" alt="">
        <div style="flex:1; min-width:240px;">
            <h2><?= e($user['full_name']) ?></h2>
            <p class="text-muted mb-0"><?= e($user['username']) ?> &middot; <?= e($user['email'] ?: '-') ?></p>
            <span class="badge badge-green" style="margin-top:8px; display:inline-block;"><?= e($user['role_name']) ?></span>

            <?php if ($student): ?>
                <table style="margin-top:16px; font-size:14px;">
                    <tr><td class="text-muted" style="padding:4px 12px 4px 0;">NIS / NISN</td><td><?= e($student['nis']) ?> / <?= e($student['nisn']) ?></td></tr>
                    <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Kelas</td><td><?= e($student['class_name'] ?? '-') ?></td></tr>
                    <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Jurusan</td><td><?= e($student['major_name'] ?? '-') ?></td></tr>
                </table>
            <?php elseif ($teacher): ?>
                <table style="margin-top:16px; font-size:14px;">
                    <tr><td class="text-muted" style="padding:4px 12px 4px 0;">NIP</td><td><?= e($teacher['nip'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Jabatan</td><td><?= e($teacher['position'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Mapel</td><td><?= e($teacher['subject'] ?: '-') ?></td></tr>
                </table>
            <?php endif; ?>
        </div>

        <?php if ($qrToken): ?>
        <div class="card" style="box-shadow:none; border-style:dashed; text-align:center; min-width:220px;">
            <h4>QR Presensi Saya</h4>
            <img src="/profile/qr-image" alt="QR Code" style="width:200px; height:200px; margin:12px 0;">
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($showDefaultPasswordWarning)): ?>
<div class="alert alert-warning">Akun Anda masih menggunakan password default. Segera ganti password di bawah ini demi keamanan.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>Ganti Password</h3></div>
    <form method="POST" action="/profile/change-password" style="max-width:420px;">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="current_password">Password Saat Ini *</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="new_password">Password Baru * <span class="text-muted">(min. 6 karakter)</span></label>
            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="new_password_confirmation">Konfirmasi Password Baru *</label>
            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required minlength="6" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
    </form>
</div>
