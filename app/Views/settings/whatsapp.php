<div class="card">
    <div class="card-header"><h3>Pengaturan WhatsApp</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Konfigurasi ini disimpan di database (tabel <code>whatsapp_settings</code>), bukan file <code>.env</code>, sehingga dapat diubah kapan saja tanpa deploy ulang.</p>

    <form method="POST" action="/settings/whatsapp/update">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="api_url">API URL Provider</label>
            <input type="text" id="api_url" name="api_url" class="form-control" placeholder="https://api.provider.com/send" value="<?= e($settings['api_url'] ?? '') ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="api_key">API Key</label>
                <input type="text" id="api_key" name="api_key" class="form-control" value="<?= e($settings['api_key'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="sender">Nomor Pengirim</label>
                <input type="text" id="sender" name="sender" class="form-control" placeholder="628xxxxxxxxxx" value="<?= e($settings['sender'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="flex items-center gap-2" style="font-weight:600;">
                <input type="checkbox" name="is_active" value="1" style="width:auto;" <?= !empty($settings['is_active']) ? 'checked' : '' ?>>
                Aktifkan pengiriman WhatsApp otomatis
            </label>
        </div>
        <div class="form-group">
            <label class="flex items-center gap-2" style="font-weight:600;">
                <input type="checkbox" name="notify_on_in" value="1" style="width:auto;" <?= ($settings['notify_on_in'] ?? 1) ? 'checked' : '' ?>>
                Kirim notifikasi saat presensi masuk (hadir)
            </label>
        </div>
        <div class="form-group">
            <label class="flex items-center gap-2" style="font-weight:600;">
                <input type="checkbox" name="notify_on_late" value="1" style="width:auto;" <?= ($settings['notify_on_late'] ?? 1) ? 'checked' : '' ?>>
                Kirim notifikasi saat terlambat
            </label>
        </div>
        <div class="form-group">
            <label class="flex items-center gap-2" style="font-weight:600;">
                <input type="checkbox" name="notify_on_out" value="1" style="width:auto;" <?= !empty($settings['notify_on_out']) ? 'checked' : '' ?>>
                Kirim notifikasi saat presensi pulang
            </label>
        </div>

        <div class="alert alert-info">Jika API URL/Key kosong, sistem tetap mencatat antrian pesan di Log WhatsApp dengan status <strong>failed</strong> beserta alasannya — presensi tidak akan gagal karena WhatsApp belum dikonfigurasi.</div>

        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </form>
</div>
