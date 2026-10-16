<div class="card">
    <div class="card-header">
        <h3>Rekap Bulanan</h3>
        <div class="flex gap-2">
            <a href="/reports/monthly/export?format=excel&<?= http_build_query(['year' => $year, 'month' => $month, 'class_id' => $classId]) ?>" class="btn btn-sm btn-outline"><i data-lucide="file-spreadsheet"></i> Excel</a>
            <a href="/reports/monthly/export?format=pdf&<?= http_build_query(['year' => $year, 'month' => $month, 'class_id' => $classId]) ?>" target="_blank" class="btn btn-sm btn-red"><i data-lucide="file-text"></i> PDF</a>
        </div>
    </div>

    <form method="GET" action="/reports/monthly" class="flex gap-3" style="flex-wrap:wrap; margin-bottom:18px;">
        <select name="month" class="form-control" style="max-width:160px;">
            <?php $monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; ?>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
            <?php endfor; ?>
        </select>
        <input type="number" name="year" class="form-control" style="max-width:120px;" value="<?= (int) $year ?>" min="2020" max="2100">
        <select name="class_id" class="form-control" style="max-width:180px;">
            <option value="">Semua Kelas</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string) $classId === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Tampilkan</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Hadir</th><th>Terlambat</th><th>Izin</th><th>Sakit</th><th>Alpa</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8" class="text-muted" style="text-align:center; padding:24px;">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['class_name'] ?? '-') ?></td>
                    <td><span class="badge badge-green"><?= (int) $r['present_count'] ?></span></td>
                    <td><span class="badge badge-yellow"><?= (int) $r['late_count'] ?></span></td>
                    <td><span class="badge badge-blue"><?= (int) $r['permission_count'] ?></span></td>
                    <td><span class="badge badge-purple"><?= (int) $r['sick_count'] ?></span></td>
                    <td><span class="badge badge-red"><?= (int) $r['absent_count'] ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
