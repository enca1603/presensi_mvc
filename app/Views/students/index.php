<div class="card">
    <div class="card-header">
        <h3>Data Siswa</h3>
        <a href="/students/create" class="btn btn-primary"><i data-lucide="plus"></i> Tambah Siswa</a>
    </div>

    <form method="GET" action="/students" class="flex gap-3" style="flex-wrap:wrap; margin-bottom:18px;">
        <input type="text" name="search" class="form-control" style="max-width:240px;" placeholder="Cari nama / NIS / NISN" value="<?= e($filters['search'] ?? '') ?>">
        <select name="class_id" class="form-control" style="max-width:180px;">
            <option value="">Semua Kelas</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string) ($filters['class_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="major_id" class="form-control" style="max-width:200px;">
            <option value="">Semua Jurusan</option>
            <?php foreach ($majors as $m): ?>
                <option value="<?= (int) $m['id'] ?>" <?= (string) ($filters['major_id'] ?? '') === (string) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-control" style="max-width:160px;">
            <option value="">Semua Status</option>
            <?php foreach (['active' => 'Aktif', 'graduated' => 'Lulus', 'transferred' => 'Pindah', 'inactive' => 'Nonaktif'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>Foto</th><th>NIS</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Jurusan</th><th>Wali</th><th>Status</th><th>QR</th><th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr><td colspan="10" class="text-muted" style="text-align:center; padding:24px;">Belum ada data siswa.</td></tr>
            <?php endif; ?>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><img class="avatar" src="<?= e($s['photo'] ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($s['full_name'])) ?>" alt="" onerror="this.src='https://api.dicebear.com/7.x/initials/svg?seed=<?= urlencode($s['full_name']) ?>'"></td>
                    <td><?= e($s['nis']) ?></td>
                    <td><?= e($s['nisn']) ?></td>
                    <td><a href="/students/<?= (int) $s['id'] ?>"><?= e($s['full_name']) ?></a></td>
                    <td><?= e($s['class_name'] ?? '-') ?></td>
                    <td><?= e($s['major_name'] ?? '-') ?></td>
                    <td><?= e($s['guardian_name'] ?? '-') ?></td>
                    <td>
                        <?php
                        $statusMap = ['active' => 'badge-green|Aktif', 'graduated' => 'badge-blue|Lulus', 'transferred' => 'badge-yellow|Pindah', 'inactive' => 'badge-red|Nonaktif'];
                        [$cls, $label] = explode('|', $statusMap[$s['status']] ?? 'badge-gray|-');
                        ?>
                        <span class="badge <?= $cls ?>"><?= $label ?></span>
                    </td>
                    <td><span class="badge <?= $s['qr_status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= $s['qr_status'] === 'active' ? 'Aktif' : 'Belum' ?></span></td>
                    <td class="flex gap-2">
                        <a href="/students/<?= (int) $s['id'] ?>/edit" class="btn btn-sm btn-blue" aria-label="Edit"><i data-lucide="pencil"></i></a>
                        <form method="POST" action="/students/<?= (int) $s['id'] ?>/delete" onsubmit="return confirm('Hapus siswa ini?');">
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
            <?php
            $query = array_filter(array_merge($filters, ['page' => $p]));
            $url = '/students?' . http_build_query($query);
            ?>
            <a href="<?= e($url) ?>" class="<?= $p === $pagination['page'] ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <p class="text-sm text-muted">Total <?= (int) $pagination['total'] ?> siswa</p>
</div>
