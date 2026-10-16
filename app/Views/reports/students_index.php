<div class="card">
    <div class="card-header"><h3>Rekap Siswa</h3></div>
    <form method="GET" action="/reports/students" class="flex gap-3" style="margin-bottom:18px;">
        <input type="text" name="search" class="form-control" style="max-width:280px;" placeholder="Cari nama / NIS" value="<?= e($search ?? '') ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Cari</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>NIS</th><th>Nama</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= e($s['nis']) ?></td>
                    <td><?= e($s['full_name']) ?></td>
                    <td><a href="/reports/students/<?= (int) $s['id'] ?>" class="btn btn-sm btn-blue"><i data-lucide="file-search"></i> Lihat Rekap</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
