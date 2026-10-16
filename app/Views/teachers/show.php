<div class="card">
    <div class="card-header">
        <h3>Detail Guru</h3>
        <a href="/teachers" class="btn btn-outline"><i data-lucide="arrow-left"></i> Kembali</a>
    </div>
    <div class="flex gap-3" style="align-items:flex-start; flex-wrap:wrap;">
        <img class="avatar-lg" src="<?= e($teacher['photo'] ?: '') ?>" onerror="this.src='https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($teacher['full_name']) ?>'" alt="">
        <div style="flex:1; min-width:240px;">
            <h2><?= e($teacher['full_name']) ?></h2>
            <p class="text-muted mb-0">NIP <?= e($teacher['nip'] ?: '-') ?> &middot; NUPTK <?= e($teacher['nuptk'] ?: '-') ?></p>
            <div class="flex gap-2" style="margin-top:10px;">
                <span class="badge badge-blue"><?= e($teacher['position'] ?: '-') ?></span>
                <span class="badge badge-purple"><?= e($teacher['subject'] ?: '-') ?></span>
                <span class="badge badge-green"><?= e(ucfirst($teacher['status'])) ?></span>
            </div>
            <table style="margin-top:16px; font-size:14px;">
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Jenis Kelamin</td><td><?= $teacher['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Tempat, Tgl Lahir</td><td><?= e($teacher['birth_place']) ?>, <?= format_date($teacher['birth_date']) ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Alamat</td><td><?= e($teacher['address']) ?></td></tr>
                <tr><td class="text-muted" style="padding:4px 12px 4px 0;">Kontak</td><td><?= e($teacher['phone'] ?: '-') ?> / <?= e($teacher['email'] ?: '-') ?></td></tr>
            </table>
            <a href="/teachers/<?= (int) $teacher['id'] ?>/edit" class="btn btn-blue" style="margin-top:16px;"><i data-lucide="pencil"></i> Edit Data</a>
        </div>

        <div class="card" style="box-shadow:none; border-style:dashed; text-align:center; min-width:220px;">
            <h4>QR Code Presensi</h4>
            <?php if ($token): ?>
                <img src="/teachers/<?= (int) $teacher['id'] ?>/qr-image" alt="QR Code" style="width:200px; height:200px; margin:12px 0;">
                <p class="text-sm text-muted"><?= e($token) ?></p>
                <a href="/teachers/<?= (int) $teacher['id'] ?>/qr-image" download="qr-<?= e($teacher['nip'] ?: $teacher['id']) ?>.png" class="btn btn-sm btn-outline"><i data-lucide="download"></i> Download</a>
            <?php else: ?>
                <p class="text-muted">Belum ada QR aktif.</p>
            <?php endif; ?>
            <form method="POST" action="/teachers/<?= (int) $teacher['id'] ?>/regenerate-qr" onsubmit="return confirm('QR lama akan dinonaktifkan. Lanjutkan?');" style="margin-top:10px;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-yellow w-full"><i data-lucide="refresh-cw"></i> Regenerate QR</button>
            </form>
            <a href="/idcard/teachers/<?= (int) $teacher['id'] ?>" target="_blank" class="btn btn-sm btn-blue w-full" style="margin-top:8px;"><i data-lucide="printer"></i> Cetak Kartu ID</a>
        </div>
    </div>
</div>
