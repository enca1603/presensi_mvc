<div class="card">
    <div class="card-header"><h3>Pengaturan Sekolah</h3></div>

    <form method="POST" action="/settings/school/update" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="flex gap-3" style="align-items:center; margin-bottom:16px;">
            <img class="avatar-lg" src="<?= e($school['logo'] ?? '') ?>" onerror="this.style.display='none'" alt="">
            <div class="form-group mb-0" style="flex:1;">
                <label for="logo">Logo Sekolah</label>
                <input type="file" id="logo" name="logo" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="school_name">Nama Sekolah *</label>
                <input type="text" id="school_name" name="school_name" class="form-control" required value="<?= e($school['school_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="npsn">NPSN</label>
                <input type="text" id="npsn" name="npsn" class="form-control" value="<?= e($school['npsn'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" class="form-control" rows="2"><?= e($school['address'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone">Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= e($school['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($school['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="principal_name">Nama Kepala Sekolah</label>
            <input type="text" id="principal_name" name="principal_name" class="form-control" value="<?= e($school['principal_name'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
