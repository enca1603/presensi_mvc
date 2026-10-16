<?php $isEdit = !empty($device); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Kiosk' : 'Tambah Kiosk' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/settings/kiosks/' . $device['id'] . '/update' : '/settings/kiosks/store' ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Nama Kiosk *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= e($isEdit ? $device['name'] : old('name')) ?>" placeholder="Gerbang Utama">
            </div>
            <div class="form-group">
                <label for="device_code">Kode Kiosk *</label>
                <input type="text" id="device_code" name="device_code" class="form-control" required value="<?= e($isEdit ? $device['device_code'] : old('device_code')) ?>" placeholder="KIOSK-001" style="text-transform:uppercase;">
            </div>
        </div>

        <div class="form-group">
            <label for="location">Lokasi</label>
            <input type="text" id="location" name="location" class="form-control" value="<?= e($isEdit ? $device['location'] : old('location')) ?>">
        </div>

        <div class="form-group">
            <label for="pin"><?= $isEdit ? 'Reset PIN (opsional)' : 'PIN *' ?></label>
            <input type="text" id="pin" name="pin" class="form-control" inputmode="numeric" <?= $isEdit ? '' : 'required' ?> placeholder="<?= $isEdit ? 'Kosongkan jika tidak ingin mengubah PIN' : '4-12 digit' ?>">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?= ($isEdit ? $device['status'] : old('status', 'active')) === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= ($isEdit ? $device['status'] : old('status')) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/settings/kiosks" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
