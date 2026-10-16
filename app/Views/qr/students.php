<div class="card">
    <div class="card-header">
        <h3>QR Code Siswa</h3>
    </div>

    <form method="GET" action="/qr/students" class="flex gap-3" style="margin-bottom:18px;">
        <input type="text" name="search" class="form-control" style="max-width:280px;" placeholder="Cari nama / NIS" value="<?= e($search ?? '') ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Cari</button>
        <a href="/qr/students/print-bulk?ids=<?= e(implode(',', array_column($rows, 'id'))) ?>" target="_blank" class="btn btn-yellow" style="margin-left:auto;"><i data-lucide="printer"></i> Cetak Semua yang Tampil</a>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Foto</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Status QR</th><th>Dibuat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-muted" style="text-align:center; padding:24px;">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><img class="avatar" src="<?= e($r['photo'] ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($r['full_name'])) ?>" alt=""></td>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['class_name'] ?? '-') ?></td>
                    <td><span class="badge <?= $r['qr_status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= $r['qr_status'] === 'active' ? 'Aktif' : 'Belum Ada' ?></span></td>
                    <td class="text-sm text-muted"><?= $r['generated_at'] ? format_date($r['generated_at'], 'd/m/Y H:i') : '-' ?></td>
                    <td class="flex gap-2">
                        <a href="/students/<?= (int) $r['id'] ?>/qr-image" download="qr-<?= e($r['nis']) ?>.png" class="btn btn-sm btn-outline" aria-label="Download"><i data-lucide="download"></i></a>
                        <a href="/qr/students/<?= (int) $r['id'] ?>/print" target="_blank" class="btn btn-sm btn-blue" aria-label="Print"><i data-lucide="printer"></i></a>
                        <form method="POST" action="/qr/students/<?= (int) $r['id'] ?>/regenerate" onsubmit="return confirm('QR lama akan dinonaktifkan. Lanjutkan?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-yellow" aria-label="Regenerate"><i data-lucide="refresh-cw"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-sm text-muted">Menampilkan <?= count($rows) ?> data (maks 200). Gunakan pencarian untuk mempersempit hasil.</p>
</div>
