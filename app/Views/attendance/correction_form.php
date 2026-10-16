<div class="card">
    <div class="card-header"><h3>Koreksi Presensi — <?= e($attendance['person_name']) ?></h3></div>

    <form method="POST" action="/attendance/corrections/<?= (int) $attendance['id'] ?>/update">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="attendance_date">Tanggal *</label>
                <input type="date" id="attendance_date" name="attendance_date" class="form-control" required value="<?= e($attendance['attendance_date']) ?>">
            </div>
            <div class="form-group">
                <label for="scan_time">Jam *</label>
                <input type="time" id="scan_time" name="scan_time" class="form-control" step="1" required value="<?= e($attendance['scan_time']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" class="form-control" required>
                <?php foreach (['present' => 'Hadir', 'late' => 'Terlambat', 'permission' => 'Izin', 'sick' => 'Sakit', 'absent' => 'Alpa', 'official_duty' => 'Dinas'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $attendance['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Keterangan</label>
            <textarea id="notes" name="notes" class="form-control" rows="2"><?= e($attendance['notes']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="reason">Alasan Koreksi * <span class="text-muted">(wajib diisi, akan tercatat di audit log)</span></label>
            <textarea id="reason" name="reason" class="form-control" rows="2" required></textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan Koreksi</button>
            <a href="/attendance/corrections" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
