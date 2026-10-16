<?php $isEdit = !empty($teacher); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Guru' : 'Tambah Guru' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/teachers/' . $teacher['id'] . '/update' : '/teachers/store' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="nip">NIP</label>
                <input type="text" id="nip" name="nip" class="form-control" value="<?= e($isEdit ? $teacher['nip'] : old('nip')) ?>">
            </div>
            <div class="form-group">
                <label for="nuptk">NUPTK</label>
                <input type="text" id="nuptk" name="nuptk" class="form-control" value="<?= e($isEdit ? $teacher['nuptk'] : old('nuptk')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="full_name">Nama Lengkap *</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= e($isEdit ? $teacher['full_name'] : old('full_name')) ?>">
            </div>
            <div class="form-group">
                <label for="gender">Jenis Kelamin *</label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="L" <?= ($isEdit ? $teacher['gender'] : old('gender')) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= ($isEdit ? $teacher['gender'] : old('gender')) === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="birth_place">Tempat Lahir</label>
                <input type="text" id="birth_place" name="birth_place" class="form-control" value="<?= e($isEdit ? $teacher['birth_place'] : old('birth_place')) ?>">
            </div>
            <div class="form-group">
                <label for="birth_date">Tanggal Lahir</label>
                <input type="date" id="birth_date" name="birth_date" class="form-control" value="<?= e($isEdit ? $teacher['birth_date'] : old('birth_date')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="position">Jabatan</label>
                <input type="text" id="position" name="position" class="form-control" value="<?= e($isEdit ? $teacher['position'] : old('position')) ?>">
            </div>
            <div class="form-group">
                <label for="subject">Mata Pelajaran</label>
                <input type="text" id="subject" name="subject" class="form-control" value="<?= e($isEdit ? $teacher['subject'] : old('subject')) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone">No. Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= e($isEdit ? $teacher['phone'] : old('phone')) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($isEdit ? $teacher['email'] : old('email')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" class="form-control" rows="2"><?= e($isEdit ? $teacher['address'] : old('address')) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <?php foreach (['active' => 'Aktif', 'inactive' => 'Nonaktif', 'resigned' => 'Berhenti'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($isEdit ? $teacher['status'] : old('status', 'active')) === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="photo">Foto (JPG/PNG/WEBP, maks 2MB)</label>
            <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/teachers" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
