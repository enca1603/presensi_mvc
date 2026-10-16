<div class="card">
    <div class="card-header"><h3>Koreksi Presensi</h3></div>

    <form method="GET" action="/attendance/corrections" class="flex gap-3" style="margin-bottom:18px;">
        <input type="date" name="date" class="form-control" style="max-width:200px;" value="<?= e($date) ?>">
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Tampilkan</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Jam</th><th>Jenis</th><th>Nama</th><th>Tipe</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="text-muted" style="text-align:center; padding:24px;">Belum ada presensi pada tanggal ini.</td></tr>
            <?php endif; ?>
            <?php $statusMap = ['present' => 'badge-green|Hadir', 'late' => 'badge-yellow|Terlambat', 'permission' => 'badge-blue|Izin', 'sick' => 'badge-purple|Sakit', 'absent' => 'badge-red|Alpa', 'official_duty' => 'badge-blue|Dinas']; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['scan_time']) ?></td>
                    <td><?= e($r['person_type']) ?></td>
                    <td><?= e($r['person_name']) ?></td>
                    <td><span class="badge badge-gray"><?= $r['type'] === 'in' ? 'Masuk' : 'Pulang' ?></span></td>
                    <td><?php [$cls, $label] = explode('|', $statusMap[$r['status']] ?? 'badge-gray|-'); ?><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                    <td><a href="/attendance/corrections/<?= (int) $r['id'] ?>/edit" class="btn btn-sm btn-blue"><i data-lucide="rotate-ccw"></i> Koreksi</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
