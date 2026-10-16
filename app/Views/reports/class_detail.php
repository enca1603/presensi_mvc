<div class="card">
    <div class="card-header">
        <h3>Rekap Kelas <?= e($class['name']) ?></h3>
        <div class="flex gap-2">
            <a href="/reports/classes/<?= (int) $class['id'] ?>/export?format=excel&<?= http_build_query(['start' => $start, 'end' => $end]) ?>" class="btn btn-sm btn-outline"><i data-lucide="file-spreadsheet"></i> Excel</a>
            <a href="/reports/classes/<?= (int) $class['id'] ?>/export?format=pdf&<?= http_build_query(['start' => $start, 'end' => $end]) ?>" target="_blank" class="btn btn-sm btn-red"><i data-lucide="file-text"></i> PDF</a>
        </div>
    </div>

    <form method="GET" action="/reports/classes/<?= (int) $class['id'] ?>" class="flex gap-3" style="margin-bottom:18px;">
        <input type="date" name="start" class="form-control" style="max-width:180px;" value="<?= e($start) ?>">
        <input type="date" name="end" class="form-control" style="max-width:180px;" value="<?= e($end) ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Tampilkan</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>NIS</th><th>Nama</th><th>Hadir</th><th>Terlambat</th><th>Izin</th><th>Sakit</th><th>Alpa</th></tr></thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr><td colspan="7" class="text-muted" style="text-align:center; padding:24px;">Tidak ada siswa di kelas ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= e($s['nis']) ?></td>
                    <td><?= e($s['full_name']) ?></td>
                    <td><span class="badge badge-green"><?= (int) $s['present_count'] ?></span></td>
                    <td><span class="badge badge-yellow"><?= (int) $s['late_count'] ?></span></td>
                    <td><span class="badge badge-blue"><?= (int) $s['permission_count'] ?></span></td>
                    <td><span class="badge badge-purple"><?= (int) $s['sick_count'] ?></span></td>
                    <td><span class="badge badge-red"><?= (int) $s['absent_count'] ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
