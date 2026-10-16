<?php $statusMap = ['present' => 'badge-green|Hadir', 'late' => 'badge-yellow|Terlambat', 'permission' => 'badge-blue|Izin', 'sick' => 'badge-purple|Sakit', 'absent' => 'badge-red|Alpa', 'official_duty' => 'badge-blue|Dinas']; ?>
<div class="card">
    <div class="card-header">
        <h3><?= e($teacher['full_name']) ?> <span class="text-muted text-sm">(<?= e($teacher['nip'] ?: '-') ?>)</span></h3>
        <div class="flex gap-2">
            <a href="/reports/teachers/<?= (int) $teacher['id'] ?>/export?format=excel&<?= http_build_query(['start' => $start, 'end' => $end]) ?>" class="btn btn-sm btn-outline"><i data-lucide="file-spreadsheet"></i> Excel</a>
            <a href="/reports/teachers/<?= (int) $teacher['id'] ?>/export?format=pdf&<?= http_build_query(['start' => $start, 'end' => $end]) ?>" target="_blank" class="btn btn-sm btn-red"><i data-lucide="file-text"></i> PDF</a>
        </div>
    </div>

    <form method="GET" action="/reports/teachers/<?= (int) $teacher['id'] ?>" class="flex gap-3" style="margin-bottom:18px;">
        <input type="date" name="start" class="form-control" style="max-width:180px;" value="<?= e($start) ?>">
        <input type="date" name="end" class="form-control" style="max-width:180px;" value="<?= e($end) ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Tampilkan</button>
    </form>

    <div class="stat-grid" style="margin-bottom:18px;">
        <div class="stat-card" style="background:var(--primary-light);"><div class="label">Hadir</div><div class="value"><?= (int) $summary['present'] ?></div></div>
        <div class="stat-card" style="background:var(--yellow);"><div class="label">Terlambat</div><div class="value"><?= (int) $summary['late'] ?></div></div>
        <div class="stat-card" style="background:var(--blue);"><div class="label">Dinas</div><div class="value"><?= (int) $summary['official_duty'] ?></div></div>
        <div class="stat-card" style="background:var(--purple);"><div class="label">Sakit</div><div class="value"><?= (int) $summary['sick'] ?></div></div>
        <div class="stat-card" style="background:var(--red);"><div class="label">Alpa</div><div class="value"><?= (int) $summary['absent'] ?></div></div>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Tanggal</th><th>Tipe</th><th>Jam</th><th>Status</th><th>Metode</th></tr></thead>
            <tbody>
            <?php if (empty($history)): ?>
                <tr><td colspan="5" class="text-muted" style="text-align:center; padding:24px;">Tidak ada riwayat presensi pada rentang ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= format_date($h['attendance_date']) ?></td>
                    <td><span class="badge badge-gray"><?= $h['type'] === 'in' ? 'Masuk' : 'Pulang' ?></span></td>
                    <td><?= e($h['scan_time']) ?></td>
                    <td><?php [$cls, $label] = explode('|', $statusMap[$h['status']] ?? 'badge-gray|-'); ?><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                    <td class="text-sm text-muted"><?= $h['method'] === 'qr' ? 'QR Scan' : 'Manual' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
