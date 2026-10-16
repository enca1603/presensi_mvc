<?php $isEdit = !empty($class); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Kelas' : 'Tambah Kelas' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/classes/' . $class['id'] . '/update' : '/classes/store' ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Nama Kelas * <span class="text-muted">(contoh: X RPL 1)</span></label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= e($isEdit ? $class['name'] : old('name')) ?>">
            </div>
            <div class="form-group">
                <label for="grade">Tingkat * <span class="text-muted">(X / XI / XII)</span></label>
                <input type="text" id="grade" name="grade" class="form-control" required value="<?= e($isEdit ? $class['grade'] : old('grade')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="major_id">Jurusan</label>
                <select id="major_id" name="major_id" class="form-control">
                    <option value="">- Pilih Jurusan -</option>
                    <?php foreach ($majors as $m): ?>
                        <option value="<?= (int) $m['id'] ?>" <?= (string) ($isEdit ? $class['major_id'] : old('major_id')) === (string) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="academic_year_id">Tahun Ajaran *</label>
                <select id="academic_year_id" name="academic_year_id" class="form-control" required>
                    <option value="">- Pilih Tahun Ajaran -</option>
                    <?php foreach ($academicYears as $ay): ?>
                        <option value="<?= (int) $ay['id'] ?>" <?= (string) ($isEdit ? $class['academic_year_id'] : old('academic_year_id')) === (string) $ay['id'] ? 'selected' : '' ?>><?= e($ay['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="homeroom_teacher_id">Wali Kelas</label>
            <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="form-control">
                <option value="">- Pilih Wali Kelas -</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (string) ($isEdit ? $class['homeroom_teacher_id'] : old('homeroom_teacher_id')) === (string) $t['id'] ? 'selected' : '' ?>><?= e($t['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= ($isEdit ? $class['status'] : old('status', 'active')) === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($isEdit ? $class['status'] : old('status')) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/classes" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
