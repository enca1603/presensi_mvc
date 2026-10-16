<?php $isEdit = !empty($year); ?>
<div class="card">
    <div class="card-header"><h3><?= $isEdit ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' ?></h3></div>

    <form method="POST" action="<?= $isEdit ? '/academic-years/' . $year['id'] . '/update' : '/academic-years/store' ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="name">Nama * <span class="text-muted">(contoh: 2025/2026)</span></label>
            <input type="text" id="name" name="name" class="form-control" required value="<?= e($isEdit ? $year['name'] : old('name')) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="start_date">Tanggal Mulai *</label>
                <input type="date" id="start_date" name="start_date" class="form-control" required value="<?= e($isEdit ? $year['start_date'] : old('start_date')) ?>">
            </div>
            <div class="form-group">
                <label for="end_date">Tanggal Selesai *</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required value="<?= e($isEdit ? $year['end_date'] : old('end_date')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="flex items-center gap-2" style="font-weight:600;">
                <input type="checkbox" name="is_active" value="1" <?= ($isEdit ? $year['is_active'] : old('is_active')) ? 'checked' : '' ?> style="width:auto;">
                Jadikan tahun ajaran aktif
            </label>
            <p class="form-hint">Mengaktifkan tahun ajaran ini akan menonaktifkan tahun ajaran lain secara otomatis.</p>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/academic-years" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
