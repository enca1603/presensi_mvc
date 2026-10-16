<div class="card">
    <div class="card-header">
        <h3>Data Kelas</h3>
        <a href="/classes/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Kelas</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Nama Kelas</th><th>Tingkat</th><th>Jurusan</th><th>Tahun Ajaran</th><th>Wali Kelas</th><th>Jumlah Siswa</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($classes)): ?>
                <tr><td colspan="8" class="text-muted" style="text-align:center; padding:24px;">Belum ada data kelas.</td></tr>
            <?php endif; ?>
            <?php foreach ($classes as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['grade']) ?></td>
                    <td><?= e($c['major_name'] ?? '-') ?></td>
                    <td><?= e($c['academic_year_name'] ?? '-') ?></td>
                    <td><?= e($c['homeroom_name'] ?? '-') ?></td>
                    <td><?= (int) $c['student_count'] ?></td>
                    <td><span class="badge <?= $c['status'] === 'active' ? 'badge-green' : 'badge-red' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
                    <td class="flex gap-2">
                        <a href="/classes/<?= (int) $c['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/classes/<?= (int) $c['id'] ?>/delete" onsubmit="return confirm('Hapus kelas ini?');">
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
