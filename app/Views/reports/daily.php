<?php $statusMap = ['present' => 'badge-green|Hadir', 'late' => 'badge-yellow|Terlambat', 'permission' => 'badge-blue|Izin', 'sick' => 'badge-purple|Sakit', 'absent' => 'badge-gray|Belum Hadir']; ?>
<div class="card">
    <div class="card-header">
        <h3>Rekap Harian</h3>
        <div class="flex gap-2">
            <a href="/reports/daily/export?format=excel&<?= http_build_query(['date' => $date, 'class_id' => $classId, 'major_id' => $majorId]) ?>" class="btn btn-sm btn-outline"><i data-lucide="file-spreadsheet"></i> Excel</a>
            <a href="/reports/daily/export?format=pdf&<?= http_build_query(['date' => $date, 'class_id' => $classId, 'major_id' => $majorId]) ?>" target="_blank" class="btn btn-sm btn-red"><i data-lucide="file-text"></i> PDF</a>
        </div>
    </div>

    <form method="GET" action="/reports/daily" class="flex gap-3" style="flex-wrap:wrap; margin-bottom:18px;">
        <input type="date" name="date" class="form-control" style="max-width:200px;" value="<?= e($date) ?>">
        <select name="class_id" class="form-control" style="max-width:180px;">
            <option value="">Semua Kelas</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string) $classId === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="major_id" class="form-control" style="max-width:200px;">
            <option value="">Semua Jurusan</option>
            <?php foreach ($majors as $m): ?>
                <option value="<?= (int) $m['id'] ?>" <?= (string) $majorId === (string) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Tampilkan</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Status</th><th>Jam Masuk</th><th>Jam Pulang</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-muted" style="text-align:center; padding:24px;">Tidak ada data siswa.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['class_name'] ?? '-') ?></td>
                    <td><?php [$cls, $label] = explode('|', $statusMap[$r['status_final']] ?? 'badge-gray|Belum Hadir'); ?><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                    <td><?= e($r['time_in'] ?? '-') ?></td>
                    <td><?= e($r['time_out'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-sm text-muted">Total <?= count($rows) ?> siswa &middot; <?= format_date_id($date) ?></p>
</div>
