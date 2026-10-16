<div class="card">
    <div class="card-header">
        <h3>Data Wali Murid</h3>
        <a href="/guardians/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Wali</a>
    </div>

    <form method="GET" action="/guardians" class="flex gap-3" style="margin-bottom:18px;">
        <input type="text" name="search" class="form-control" style="max-width:280px;" placeholder="Cari nama / no. WhatsApp" value="<?= e($search ?? '') ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Cari</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Nama</th><th>Hubungan</th><th>No. WhatsApp</th><th>Email</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($guardians)): ?>
                <tr><td colspan="6" class="text-muted" style="text-align:center; padding:24px;">Belum ada data wali murid.</td></tr>
            <?php endif; ?>
            <?php $relLabel = ['father' => 'Ayah', 'mother' => 'Ibu', 'guardian' => 'Wali']; ?>
            <?php foreach ($guardians as $g): ?>
                <tr>
                    <td><?= e($g['name']) ?></td>
                    <td><?= $relLabel[$g['relationship']] ?? '-' ?></td>
                    <td><?= e($g['phone'] ?: '-') ?></td>
                    <td><?= e($g['email'] ?: '-') ?></td>
                    <td><span class="badge <?= $g['status'] === 'active' ? 'badge-green' : 'badge-red' ?>"><?= e(ucfirst($g['status'])) ?></span></td>
                    <td class="flex gap-2">
                        <a href="/guardians/<?= (int) $g['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/guardians/<?= (int) $g['id'] ?>/delete" onsubmit="return confirm('Hapus wali murid ini?');">
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
            <a href="/guardians?<?= http_build_query(array_filter(['search' => $search, 'page' => $p])) ?>" class="<?= $p === $pagination['page'] ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <p class="text-sm text-muted">Total <?= (int) $pagination['total'] ?> wali murid</p>
</div>
