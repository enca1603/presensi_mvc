<div class="card">
    <div class="card-header"><h3>Presensi Siswa</h3></div>

    <form method="GET" action="/attendance/students" class="flex gap-3" style="flex-wrap:wrap; margin-bottom:18px;">
        <input type="date" name="date" class="form-control" style="max-width:200px;" value="<?= e($date) ?>">
        <select name="class_id" class="form-control" style="max-width:180px;">
            <option value="">Semua Kelas</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (string) $classId === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="form-control" style="max-width:160px;">
            <option value="">Semua Status</option>
            <?php foreach (['present' => 'Hadir', 'late' => 'Terlambat', 'permission' => 'Izin', 'sick' => 'Sakit', 'absent' => 'Alpa', 'official_duty' => 'Dinas'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Jam</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Tipe</th><th>Status</th><th>Metode</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-muted" style="text-align:center; padding:24px;">Belum ada presensi pada tanggal ini.</td></tr>
            <?php endif; ?>
            <?php $statusMap = ['present' => 'badge-green|Hadir', 'late' => 'badge-yellow|Terlambat', 'permission' => 'badge-blue|Izin', 'sick' => 'badge-purple|Sakit', 'absent' => 'badge-red|Alpa', 'official_duty' => 'badge-blue|Dinas']; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['scan_time']) ?></td>
                    <td><?= e($r['nis']) ?></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['class_name'] ?? '-') ?></td>
                    <td><span class="badge badge-gray"><?= $r['type'] === 'in' ? 'Masuk' : 'Pulang' ?></span></td>
                    <td><?php [$cls, $label] = explode('|', $statusMap[$r['status']] ?? 'badge-gray|-'); ?><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                    <td class="text-sm text-muted"><?= $r['method'] === 'qr' ? 'QR Scan' : 'Manual' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-sm text-muted">Total <?= count($rows) ?> data.</p>
</div>
