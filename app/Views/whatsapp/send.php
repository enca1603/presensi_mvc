<div class="card">
    <div class="card-header"><h3>Kirim WhatsApp</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Kirim pesan manual/uji coba ke nomor tujuan. Berguna untuk menguji konfigurasi provider sebelum mengandalkan notifikasi otomatis.</p>

    <form method="POST" action="/whatsapp/send-now">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="student_pick">Pilih Siswa (opsional, otomatis isi nomor wali)</label>
            <select id="student_pick" class="form-control" onchange="const opt=this.options[this.selectedIndex]; document.getElementById('phone').value = opt.dataset.phone || '';">
                <option value="">- Isi Manual -</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" data-phone="<?= e($s['phone'] ?? '') ?>"><?= e($s['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="phone">Nomor WhatsApp *</label>
            <input type="text" id="phone" name="phone" class="form-control" placeholder="628xxxxxxxxxx" required>
        </div>

        <div class="form-group">
            <label for="message">Pesan *</label>
            <textarea id="message" name="message" class="form-control" rows="4" required>Assalamu'alaikum, ini adalah pesan uji coba dari Sistem Presensi Sekolah.</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Kirim</button>
    </form>
</div>
