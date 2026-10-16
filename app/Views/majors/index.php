<div class="card">
    <div class="card-header">
        <h3>Data Jurusan</h3>
        <a href="/majors/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Jurusan</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Nama Jurusan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($majors)): ?>
                <tr><td colspan="4" class="text-muted" style="text-align:center; padding:24px;">Belum ada data jurusan.</td></tr>
            <?php endif; ?>
            <?php foreach ($majors as $m): ?>
                <tr>
                    <td><span class="badge badge-blue"><?= e($m['code']) ?></span></td>
                    <td><?= e($m['name']) ?></td>
                    <td><span class="badge <?= $m['status'] === 'active' ? 'badge-green' : 'badge-red' ?>"><?= e(ucfirst($m['status'])) ?></span></td>
                    <td class="flex gap-2">
                        <a href="/majors/<?= (int) $m['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/majors/<?= (int) $m['id'] ?>/delete" onsubmit="return confirm('Hapus jurusan ini?');">
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
