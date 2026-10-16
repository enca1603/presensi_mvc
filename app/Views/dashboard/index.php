<?php if (!empty($showDefaultPasswordWarning)): ?>
<div class="alert alert-warning">Akun Anda masih menggunakan password default. <a href="/profile" style="text-decoration:underline; font-weight:800;">Segera ganti password Anda.</a></div>
<?php endif; ?>

<div class="stat-grid" id="statGrid">
    <div class="stat-card"><div class="label">Total Siswa</div><div class="value"><?= (int) $totalStudents ?></div></div>
    <div class="stat-card"><div class="label">Total Guru</div><div class="value"><?= (int) $totalTeachers ?></div></div>
    <div class="stat-card" style="background:var(--primary-light);"><div class="label">Hadir Hari Ini</div><div class="value" data-stat="hadir"><?= (int) $hadirHariIni ?></div></div>
    <div class="stat-card" style="background:var(--yellow);"><div class="label">Terlambat</div><div class="value" data-stat="terlambat"><?= (int) $terlambat ?></div></div>
    <div class="stat-card" style="background:var(--blue);"><div class="label">Izin</div><div class="value" data-stat="izin"><?= (int) $izin ?></div></div>
    <div class="stat-card" style="background:var(--purple);"><div class="label">Sakit</div><div class="value" data-stat="sakit"><?= (int) $sakit ?></div></div>
    <div class="stat-card" style="background:var(--red);"><div class="label">Alpa</div><div class="value" data-stat="alpa"><?= (int) $alpa ?></div></div>
    <div class="stat-card"><div class="label">Belum Hadir</div><div class="value" data-stat="belum_hadir"><?= (int) $belumHadir ?></div></div>
</div>

<div class="flex gap-3" style="flex-wrap:wrap;">
    <div class="card" style="flex:2; min-width:340px;">
        <h4 style="margin-bottom:14px;">Kehadiran Siswa — 7 Hari Terakhir</h4>
        <canvas id="chartTrend" height="110"></canvas>
    </div>
    <div class="card" style="flex:1; min-width:260px;">
        <h4 style="margin-bottom:14px;">Distribusi Status (7 Hari)</h4>
        <canvas id="chartStatus" height="180"></canvas>
    </div>
</div>

<div class="flex gap-3" style="flex-wrap:wrap;">
    <div class="card" style="flex:1; min-width:340px;">
        <h4 style="margin-bottom:14px;">Rekap Kehadiran per Kelas (7 Hari)</h4>
        <canvas id="chartClass" height="140"></canvas>
    </div>
    <div class="card" style="flex:1; min-width:340px;">
        <h4 style="margin-bottom:14px;">Tren Keterlambatan — 7 Hari Terakhir</h4>
        <canvas id="chartLate" height="140"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Selamat datang, <?= e($currentUser['full_name'] ?? '') ?></h3></div>
    <p class="text-sm text-muted">Statistik di atas otomatis diperbarui setiap 15 detik tanpa reload halaman saat ada presensi baru dari Kiosk.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const palette = { green: '#79B989', greenLight: '#B8E6C1', yellow: '#FFE7A8', blue: '#CDE5FF', purple: '#DDD1FF', red: '#FFBABA', text: '#18231B' };
    Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
    Chart.defaults.color = palette.text;

    new Chart(document.getElementById('chartTrend'), {
        type: 'line',
        data: {
            labels: <?= json_encode($trend['labels']) ?>,
            datasets: [{
                label: 'Hadir',
                data: <?= json_encode($trend['values']) ?>,
                borderColor: palette.green,
                backgroundColor: palette.greenLight,
                tension: 0.3,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
            }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });

    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusDistribution['labels']) ?>,
            datasets: [{
                data: <?= json_encode($statusDistribution['values']) ?>,
                backgroundColor: [palette.green, palette.yellow, palette.blue, palette.purple, palette.red],
                borderColor: palette.text,
                borderWidth: 2,
            }],
        },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } },
    });

    new Chart(document.getElementById('chartClass'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($perClass['labels']) ?>,
            datasets: [{
                label: 'Hadir',
                data: <?= json_encode($perClass['values']) ?>,
                backgroundColor: palette.blue,
                borderColor: palette.text,
                borderWidth: 2,
                borderRadius: 6,
            }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });

    new Chart(document.getElementById('chartLate'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($lateness['labels']) ?>,
            datasets: [{
                label: 'Terlambat',
                data: <?= json_encode($lateness['values']) ?>,
                backgroundColor: palette.yellow,
                borderColor: palette.text,
                borderWidth: 2,
                borderRadius: 6,
            }],
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
    });

    async function refreshStats() {
        try {
            const res = await fetch('/api/dashboard/stats', { cache: 'no-store' });
            const json = await res.json();
            if (!json.success) return;
            Object.entries(json.data).forEach(([key, value]) => {
                const el = document.querySelector(`[data-stat="${key}"]`);
                if (el) el.textContent = value;
            });
        } catch (e) { /* silent */ }
    }

    setInterval(refreshStats, 15000);
})();
</script>
