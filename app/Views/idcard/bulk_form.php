<div class="card">
    <div class="card-header"><h3>Cetak Kartu ID</h3></div>
    <p class="text-sm text-muted" style="margin-bottom:16px;">Cetak kartu identitas siswa atau guru (85.6mm × 53.98mm, depan + belakang). Kartu belum tersedia satuan lewat halaman ini? Cetak satuan bisa dilakukan dari halaman detail Siswa/Guru atau QR Code.</p>

    <div class="flex gap-3" style="flex-wrap:wrap;">
        <div class="card" style="flex:1; min-width:300px; box-shadow:none; border-style:dashed;">
            <h4 style="margin-bottom:12px;">Cetak Kartu Siswa</h4>
            <form method="GET" action="/idcard/bulk" target="_blank">
                <input type="hidden" name="type" value="students">
                <div class="form-group">
                    <label for="scope">Cakupan</label>
                    <select id="scope" name="scope" class="form-control" onchange="document.getElementById('classWrap').style.display=this.value==='class'?'block':'none'; document.getElementById('majorWrap').style.display=this.value==='major'?'block':'none';">
                        <option value="all">Semua Siswa Aktif</option>
                        <option value="class">Per Kelas</option>
                        <option value="major">Per Jurusan</option>
                    </select>
                </div>
                <div class="form-group" id="classWrap" style="display:none;">
                    <label for="class_id">Kelas</label>
                    <select id="class_id" name="class_id" class="form-control">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" id="majorWrap" style="display:none;">
                    <label for="major_id">Jurusan</label>
                    <select id="major_id" name="major_id" class="form-control">
                        <?php foreach ($majors as $m): ?>
                            <option value="<?= (int) $m['id'] ?>"><?= e($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i data-lucide="printer"></i> Cetak Kartu Siswa</button>
            </form>
        </div>

        <div class="card" style="flex:1; min-width:300px; box-shadow:none; border-style:dashed;">
            <h4 style="margin-bottom:12px;">Cetak Kartu Guru</h4>
            <p class="text-sm text-muted" style="margin-bottom:12px;">Mencetak kartu untuk seluruh guru berstatus aktif.</p>
            <a href="/idcard/bulk?type=teachers" target="_blank" class="btn btn-primary"><i data-lucide="printer"></i> Cetak Kartu Guru</a>
        </div>
    </div>

    <div class="card" style="margin-top:18px; box-shadow:none; border-style:dashed;">
        <h4 style="margin-bottom:10px;">Cetak dari Daftar Siswa</h4>
        <p class="text-sm text-muted">Untuk memilih siswa tertentu satu per satu, buka <a href="/students">Data Siswa</a> lalu cetak kartu dari halaman detail masing-masing siswa, atau gunakan tautan cetak massal dengan daftar ID: <code>/idcard/bulk?type=students&scope=selected&ids=1,2,3</code></p>
    </div>
</div>
