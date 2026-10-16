<div class="card">
    <div class="card-header"><h3>Jam Presensi</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Semua perhitungan status Hadir/Terlambat dibaca dari pengaturan ini, tidak di-hardcode di kode program.</p>

    <form method="POST" action="/settings/attendance-hours/update">
        <?= csrf_field() ?>

        <?php foreach (['student' => 'Siswa', 'teacher' => 'Guru'] as $type => $label): ?>
            <?php $s = $settings[$type] ?? []; ?>
            <div class="card" style="box-shadow:none; border-style:dashed; margin-bottom:16px;">
                <h4 style="margin-bottom:12px;">Jam Presensi <?= $label ?></h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mulai Diterima (Jam Masuk)</label>
                        <input type="time" name="<?= $type ?>_day_start_time" class="form-control" step="1" value="<?= e($s['day_start_time'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Batas Tidak Terlambat</label>
                        <input type="time" name="<?= $type ?>_late_after_time" class="form-control" step="1" value="<?= e($s['late_after_time'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Batas Akhir Presensi Masuk</label>
                        <input type="time" name="<?= $type ?>_day_end_time" class="form-control" step="1" value="<?= e($s['day_end_time'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="flex items-center gap-2" style="margin-top:30px;">
                            <input type="checkbox" name="<?= $type ?>_out_enabled" value="1" style="width:auto;" <?= !empty($s['out_enabled']) ? 'checked' : '' ?>>
                            Aktifkan Presensi Pulang
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mulai Presensi Pulang</label>
                        <input type="time" name="<?= $type ?>_out_start_time" class="form-control" step="1" value="<?= e($s['out_start_time'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Batas Presensi Pulang</label>
                        <input type="time" name="<?= $type ?>_out_end_time" class="form-control" step="1" value="<?= e($s['out_end_time'] ?? '') ?>">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </form>
</div>
