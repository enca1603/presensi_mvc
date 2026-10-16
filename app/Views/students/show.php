<div class="card">
    <div class="card-header">
        <h3>Detail Siswa</h3>
        <a href="/students" class="btn btn-outline"><i data-lucide="arrow-left"></i> Kembali</a>
    </div>
    <div class="flex gap-3" style="align-items:flex-start; flex-wrap:wrap;">
        <img class="avatar-lg" src="<?= e($student['photo'] ?: '') ?>" onerror="this.src='https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($student['full_name']) ?>'" alt="">
        <div style="flex:1; min-width:240px;">
            <h2><?= e($student['full_name']) ?></h2>
            <p class="text-muted mb-0">NIS <?= e($student['nis']) ?> &middot; NISN <?= e($student['nisn']) ?></p>
            <div class="flex gap-2" style="margin-top:10px;">
                <span class="badge badge-blue"><?= e($student['class_name'] ?? '-') ?></span>
                <span class="badge badge-purple"><?= e($student['major_name'] ?? '-') ?></span>
                <span class="badge badge-green"><?= e(ucfirst($student['status'])) ?></span>
            </div>
            <table style="margin-top:16px; font-size:14px;">
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Jenis Kelamin</td><td><?= $student['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Tempat, Tgl Lahir</td><td><?= e($student['birth_place']) ?>, <?= format_date($student['birth_date']) ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Alamat</td><td><?= e($student['address']) ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">No. Telepon</td><td><?= e($student['phone'] ?: '-') ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Wali Murid</td><td><?= e($student['guardian_name'] ?? '-') ?> (<?= e($student['guardian_phone'] ?? '-') ?>)</td></tr>
            </table>
            <a href="/students/<?= (int) $student['id'] ?>/edit" class="btn btn-blue" style="margin-top:16px;"><i data-lucide="pencil"></i> Edit Data</a>
        </div>

        <div class="card" style="box-shadow:none; border-style:dashed; text-align:center; min-width:220px;">
            <h4>QR Code Presensi</h4>
            <?php if ($token): ?>
                <img src="/students/<?= (int) $student['id'] ?>/qr-image" alt="QR Code" style="width:200px; height:200px; margin:12px 0;">
                <p class="text-sm text-muted"><?= e($token) ?></p>
                <a href="/students/<?= (int) $student['id'] ?>/qr-image" download="qr-<?= e($student['nis']) ?>.png" class="btn btn-sm btn-outline"><i data-lucide="download"></i> Download</a>
            <?php else: ?>
                <p class="text-muted">Belum ada QR aktif.</p>
            <?php endif; ?>
            <form method="POST" action="/students/<?= (int) $student['id'] ?>/regenerate-qr" onsubmit="return confirm('QR lama akan dinonaktifkan. Lanjutkan?');" style="margin-top:10px;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-yellow w-full"><i data-lucide="refresh-cw"></i> Regenerate QR</button>
            </form>
            <a href="/idcard/students/<?= (int) $student['id'] ?>" target="_blank" class="btn btn-sm btn-blue w-full" style="margin-top:8px;"><i data-lucide="printer"></i> Cetak Kartu ID</a>
        </div>
    </div>
</div>
