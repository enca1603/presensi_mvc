<div class="card">
    <div class="card-header"><h3>Rekap Kelas</h3></div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Nama Kelas</th><th>Tingkat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($classes as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['grade']) ?></td>
                    <td><a href="/reports/classes/<?= (int) $c['id'] ?>" class="btn btn-sm btn-blue"><i data-lucide="file-search"></i> Lihat Rekap</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
