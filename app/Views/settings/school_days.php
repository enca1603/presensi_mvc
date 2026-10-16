<div class="card">
    <div class="card-header"><h3>Hari Sekolah</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Kiosk akan menolak presensi pada hari yang dinonaktifkan di sini.</p>

    <form method="POST" action="/settings/school-days/update">
        <?= csrf_field() ?>
        <?php $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu']; ?>
        <div class="flex gap-3" style="flex-wrap:wrap; margin-bottom:20px;">
            <?php foreach ($days as $d): ?>
                <label class="card" style="box-shadow:3px 3px 0 var(--border); padding:14px 20px; margin-bottom:0; cursor:pointer; <?= $d['is_active'] ? 'background:var(--primary-light);' : '' ?>">
                    <input type="checkbox" name="active_days[]" value="<?= (int) $d['day_of_week'] ?>" style="width:auto; margin-right:8px;" <?= $d['is_active'] ? 'checked' : '' ?>>
                    <?= $dayNames[$d['day_of_week']] ?>
                </label>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
