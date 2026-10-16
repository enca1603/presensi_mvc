<div class="card">
    <div class="card-header"><h3>Presensi Manual</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Semua input presensi manual akan tercatat di Audit Log.</p>

    <form method="POST" action="/attendance/manual/store">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="person_type">Jenis</label>
            <select id="person_type" name="person_type" class="form-control" required onchange="document.getElementById('student_wrap').style.display=this.value==='student'?'block':'none'; document.getElementById('teacher_wrap').style.display=this.value==='teacher'?'block':'none';">
                <option value="student">Siswa</option>
                <option value="teacher">Guru</option>
            </select>
        </div>

        <div class="form-group" id="student_wrap">
            <label for="person_id_student">Pilih Siswa</label>
            <select id="person_id_student" name="person_id" class="form-control">
                <?php foreach ($students as $s): ?>
                    <option value="<?= (int) $s['id'] ?>"><?= e($s['full_name']) ?> (<?= e($s['nis']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" id="teacher_wrap" style="display:none;">
            <label for="person_id_teacher">Pilih Guru</label>
            <select id="person_id_teacher" name="person_id_teacher" class="form-control">
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int) $t['id'] ?>"><?= e($t['full_name']) ?> (<?= e($t['nip'] ?: '-') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="attendance_date">Tanggal *</label>
                <input type="date" id="attendance_date" name="attendance_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label for="scan_time">Jam</label>
                <input type="time" id="scan_time" name="scan_time" class="form-control" step="1" value="<?= date('H:i:s') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="type">Tipe *</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="in">Masuk</option>
                    <option value="out">Pulang</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="present">Hadir</option>
                    <option value="late">Terlambat</option>
                    <option value="permission">Izin</option>
                    <option value="sick">Sakit</option>
                    <option value="absent">Alpa</option>
                    <option value="official_duty">Dinas</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Keterangan</label>
            <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Presensi</button>
    </form>
</div>
<script>
document.getElementById('person_type').addEventListener('change', function () {
    document.querySelector('[name=person_id]').name = this.value === 'student' ? 'person_id' : '_person_id_disabled';
});
document.querySelector('form').addEventListener('submit', function () {
    const type = document.getElementById('person_type').value;
    if (type === 'teacher') {
        document.getElementById('person_id_student').name = '_ignore';
        document.getElementById('person_id_teacher').name = 'person_id';
    }
});
</script>
