<div class="card">
    <div class="card-header">
        <h3>Tahun Ajaran</h3>
        <a href="/academic-years/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Tahun Ajaran</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Nama</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($years)): ?>
                <tr><td colspan="5" class="text-muted" style="text-align:center; padding:24px;">Belum ada data tahun ajaran.</td></tr>
            <?php endif; ?>
            <?php foreach ($years as $y): ?>
                <tr>
                    <td><?= e($y['name']) ?></td>
                    <td><?= format_date($y['start_date']) ?></td>
                    <td><?= format_date($y['end_date']) ?></td>
                    <td><span class="badge <?= $y['is_active'] ? 'badge-green' : 'badge-gray' ?>"><?= $y['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="flex gap-2">
                        <a href="/academic-years/<?= (int) $y['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/academic-years/<?= (int) $y['id'] ?>/delete" onsubmit="return confirm('Hapus tahun ajaran ini?');">
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
