<div class="card">
    <div class="card-header">
        <h3>Perangkat Kiosk</h3>
        <a href="/settings/kiosks/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Kiosk</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th>Status</th><th>Terakhir Aktif</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($devices)): ?>
                <tr><td colspan="6" class="text-muted" style="text-align:center; padding:24px;">Belum ada perangkat kiosk. Tambahkan agar gerbang sekolah dapat melakukan presensi QR.</td></tr>
            <?php endif; ?>
            <?php foreach ($devices as $d): ?>
                <tr>
                    <td><span class="badge badge-blue"><?= e($d['device_code']) ?></span></td>
                    <td><?= e($d['name']) ?></td>
                    <td><?= e($d['location'] ?: '-') ?></td>
                    <td><span class="badge <?= $d['status'] === 'active' ? 'badge-green' : 'badge-red' ?>"><?= e(ucfirst($d['status'])) ?></span></td>
                    <td class="text-sm text-muted"><?= $d['last_seen_at'] ? format_date($d['last_seen_at'], 'd/m/Y H:i') : 'Belum pernah' ?></td>
                    <td class="flex gap-2">
                        <a href="/settings/kiosks/<?= (int) $d['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/settings/kiosks/<?= (int) $d['id'] ?>/delete" onsubmit="return confirm('Hapus perangkat kiosk ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-red" aria-label="Hapus"><i data-lucide="trash-2"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
