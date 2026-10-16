<div class="card">
    <div class="card-header">
        <h3>QR Code Guru</h3>
    </div>

    <form method="GET" action="/qr/teachers" class="flex gap-3" style="margin-bottom:18px;">
        <input type="text" name="search" class="form-control" style="max-width:280px;" placeholder="Cari nama / NIP" value="<?= e($search ?? '') ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Cari</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Foto</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Status QR</th><th>Dibuat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-muted" style="text-align:center; padding:24px;">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><img class="avatar" src="<?= e($r['photo'] ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($r['full_name'])) ?>" alt=""></td>
                    <td><?= e($r['nip'] ?: '-') ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['position'] ?: '-') ?></td>
                    <td><span class="badge <?= $r['qr_status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= $r['qr_status'] === 'active' ? 'Aktif' : 'Belum Ada' ?></span></td>
                    <td class="text-sm text-muted"><?= $r['generated_at'] ? format_date($r['generated_at'], 'd/m/Y H:i') : '-' ?></td>
                    <td class="flex gap-2">
                        <a href="/teachers/<?= (int) $r['id'] ?>/qr-image" download="qr-<?= e($r['nip'] ?: $r['id']) ?>.png" class="btn btn-sm btn-outline" aria-label="Download"><i data-lucide="download"></i></a>
                        <a href="/qr/teachers/<?= (int) $r['id'] ?>/print" target="_blank" class="btn btn-sm btn-blue" aria-label="Print"><i data-lucide="printer"></i></a>
                        <form method="POST" action="/qr/teachers/<?= (int) $r['id'] ?>/regenerate" onsubmit="return confirm('QR lama akan dinonaktifkan. Lanjutkan?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-yellow" aria-label="Regenerate"><i data-lucide="refresh-cw"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
