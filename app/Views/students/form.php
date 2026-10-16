<?php $isEdit = !empty($student); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Siswa' : 'Tambah Siswa' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/students/' . $student['id'] . '/update' : '/students/store' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="nis">NIS *</label>
                <input type="text" id="nis" name="nis" class="form-control" required value="<?= e($isEdit ? $student['nis'] : old('nis')) ?>">
            </div>
            <div class="form-group">
                <label for="nisn">NISN *</label>
                <input type="text" id="nisn" name="nisn" class="form-control" required value="<?= e($isEdit ? $student['nisn'] : old('nisn')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= e($isEdit ? $student['full_name'] : old('full_name')) ?>">
            </div>
            <div class="form-group">
                <label for="nickname">Nama Panggilan</label>
                <input type="text" id="nickname" name="nickname" class="form-control" value="<?= e($isEdit ? $student['nickname'] : old('nickname')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="gender">Jenis Kelamin *</label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="L" <?= ($isEdit ? $student['gender'] : old('gender')) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= ($isEdit ? $student['gender'] : old('gender')) === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label for="birth_date">Tanggal Lahir</label>
                <input type="date" id="birth_date" name="birth_date" class="form-control" value="<?= e($isEdit ? $student['birth_date'] : old('birth_date')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="birth_place">Tempat Lahir</label>
                <input type="text" id="birth_place" name="birth_place" class="form-control" value="<?= e($isEdit ? $student['birth_place'] : old('birth_place')) ?>">
            </div>
            <div class="form-group">
                <label for="phone">No. Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= e($isEdit ? $student['phone'] : old('phone')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" class="form-control" rows="2"><?= e($isEdit ? $student['address'] : old('address')) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="class_id">Kelas</label>
                <select id="class_id" name="class_id" class="form-control">
                    <option value="">- Pilih Kelas -</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (string) ($isEdit ? $student['class_id'] : old('class_id')) === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="major_id">Jurusan</label>
                <select id="major_id" name="major_id" class="form-control">
                    <option value="">- Pilih Jurusan -</option>
                    <?php foreach ($majors as $m): ?>
                        <option value="<?= (int) $m['id'] ?>" <?= (string) ($isEdit ? $student['major_id'] : old('major_id')) === (string) $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="academic_year_id">Tahun Ajaran</label>
                <select id="academic_year_id" name="academic_year_id" class="form-control">
                    <option value="">- Pilih Tahun Ajaran -</option>
                    <?php foreach ($academicYears as $ay): ?>
                        <option value="<?= (int) $ay['id'] ?>" <?= (string) ($isEdit ? $student['academic_year_id'] : old('academic_year_id')) === (string) $ay['id'] ? 'selected' : '' ?>><?= e($ay['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="entry_year">Tahun Masuk</label>
                <input type="number" id="entry_year" name="entry_year" class="form-control" min="2000" max="2100" value="<?= e($isEdit ? $student['entry_year'] : old('entry_year', date('Y'))) ?>">
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="form-group">
            <label for="guardian_id">Wali Murid</label>
            <select id="guardian_id" name="guardian_id" class="form-control">
                <option value="">- Tidak Ada -</option>
                <?php foreach ($guardians as $g): ?>
                    <option value="<?= (int) $g['id'] ?>" <?= (string) $student['guardian_id'] === (string) $g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <div class="card" style="background:var(--primary-light); box-shadow:none; padding:16px;">
            <h4 style="margin-bottom:10px;">Data Wali Murid</h4>
            <div class="form-group">
                <label for="guardian_id">Pilih Wali yang Sudah Ada</label>
                <select id="guardian_id" name="guardian_id" class="form-control">
                    <option value="">- Buat Wali Baru -</option>
                    <?php foreach ($guardians as $g): ?>
                        <option value="<?= (int) $g['id'] ?>"><?= e($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="guardian_name">Nama Wali Baru</label>
                    <input type="text" id="guardian_name" name="guardian_name" class="form-control" value="<?= e(old('guardian_name')) ?>">
                </div>
                <div class="form-group">
                    <label for="guardian_relationship">Hubungan</label>
                    <select id="guardian_relationship" name="guardian_relationship" class="form-control">
                        <option value="father">Ayah</option>
                        <option value="mother">Ibu</option>
                        <option value="guardian">Wali</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="guardian_phone">No. WhatsApp Wali (format 628xxxxxxxxxx)</label>
                <input type="text" id="guardian_phone" name="guardian_phone" class="form-control" placeholder="6281234567890" value="<?= e(old('guardian_phone')) ?>">
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <?php foreach (['active' => 'Aktif', 'graduated' => 'Lulus', 'transferred' => 'Pindah', 'inactive' => 'Nonaktif'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($isEdit ? $student['status'] : old('status', 'active')) === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="photo">Foto (JPG/PNG/WEBP, maks 2MB)</label>
            <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/students" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
