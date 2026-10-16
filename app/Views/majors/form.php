<?php $isEdit = !empty($major); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Jurusan' : 'Tambah Jurusan' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/majors/' . $major['id'] . '/update' : '/majors/store' ?>">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="name">Nama Jurusan *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= e($isEdit ? $major['name'] : old('name')) ?>">
            </div>
            <div class="form-group">
                <label for="code">Kode *</label>
                <input type="text" id="code" name="code" class="form-control" required value="<?= e($isEdit ? $major['code'] : old('code')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= ($isEdit ? $major['status'] : old('status', 'active')) === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($isEdit ? $major['status'] : old('status')) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/majors" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
