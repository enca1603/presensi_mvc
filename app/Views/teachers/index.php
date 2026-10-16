<div class="card">
    <div class="card-header">
        <h3>Data Guru</h3>
        <a href="/teachers/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Guru</a>
    </div>

    <form method="GET" action="/teachers" class="flex gap-3" style="flex-wrap:wrap; margin-bottom:18px;">
        <input type="text" name="search" class="form-control" style="max-width:260px;" placeholder="Cari nama / NIP / NUPTK" value="<?= e($filters['search'] ?? '') ?>">
        <select name="status" class="form-control" style="max-width:160px;">
            <option value="">Semua Status</option>
            <?php foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif', 'resigned' => 'Berhenti'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Foto</th><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Mapel</th><th>Status</th><th>QR</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($teachers)): ?>
                <tr><td colspan="8" class="text-muted" style="text-align:center; padding:24px;">Belum ada data guru.</td></tr>
            <?php endif; ?>
            <?php foreach ($teachers as $t): ?>
                <tr>
                    <td><img class="avatar" src="<?= e($t['photo'] ?: '') ?>" onerror="this.src='https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($t['full_name']) ?>'" alt=""></td>
                    <td><?= e($t['nip'] ?: '-') ?></td>
                    <td><a href="/teachers/<?= (int) $t['id'] ?>"><?= e($t['full_name']) ?></a></td>
                    <td><?= e($t['position'] ?: '-') ?></td>
                    <td><?= e($t['subject'] ?: '-') ?></td>
                    <td><span class="badge <?= $t['status'] === 'active' ? 'badge-green' : 'badge-red' ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                    <td><span class="badge <?= $t['qr_status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= $t['qr_status'] === 'active' ? 'Aktif' : 'Belum' ?></span></td>
                    <td class="flex gap-2">
                        <a href="/teachers/<?= (int) $t['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/teachers/<?= (int) $t['id'] ?>/delete" onsubmit="return confirm('Hapus guru ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-red" aria-label="Hapus"><i data-lucide="trash-2"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php for ($p = 1; $p <= $pagination['last_page']; $p++): ?>
            <?php $url = '/teachers?' . http_build_query(array_filter(array_merge($filters, ['page' => $p]))); ?>
            <a href="<?= e($url) ?>" class="<?= $p === $pagination['page'] ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <p class="text-sm text-muted">Total <?= (int) $pagination['total'] ?> guru</p>
</div>
