<?php $isEdit = !empty($guardian); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Wali Murid' : 'Tambah Wali Murid' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/guardians/' . $guardian['id'] . '/update' : '/guardians/store' ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Nama *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= e($isEdit ? $guardian['name'] : old('name')) ?>">
            </div>
            <div class="form-group">
                <label for="relationship">Hubungan *</label>
                <select id="relationship" name="relationship" class="form-control" required>
                    <?php foreach (['father' => 'Ayah', 'mother' => 'Ibu', 'guardian' => 'Wali'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($isEdit ? $guardian['relationship'] : old('relationship')) === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone">No. WhatsApp (format 628xxxxxxxxxx)</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="6281234567890" value="<?= e($isEdit ? $guardian['phone'] : old('phone')) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($isEdit ? $guardian['email'] : old('email')) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" class="form-control" rows="2"><?= e($isEdit ? $guardian['address'] : old('address')) ?></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= ($isEdit ? $guardian['status'] : old('status', 'active')) === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($isEdit ? $guardian['status'] : old('status')) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/guardians" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
