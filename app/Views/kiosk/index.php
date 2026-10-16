<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Kiosk Presensi - <?= e($school['school_name']) ?></title>
<link rel="stylesheet" href="/assets/css/app.css">
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<style>
    html, body { height: 100%; overflow: hidden; }
    body { background: var(--bg); }
    .kiosk-wrap { height: 100vh; display: flex; flex-direction: column; }
    .kiosk-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 28px; background: #fff; border-bottom: 3px solid var(--border);
    }
    .kiosk-header .brand { display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none; }
    .kiosk-header .brand .logo-box { width: 44px; height: 44px; background: var(--primary); border: 2px solid var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .kiosk-header .clock { text-align: right; }
    .kiosk-header .clock .time { font-size: 26px; font-weight: 800; line-height: 1; }
    .kiosk-header .clock .date { font-size: 12px; opacity: .6; }
    .server-status { display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; }
    .server-status .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--primary-dark); }
    .server-status.offline .dot { background: #C0392B; }
    .kiosk-body { flex: 1; display: flex; overflow: hidden; }
    .kiosk-main { flex: 2; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; position: relative; }
    .kiosk-side { flex: 1; max-width: 340px; border-left: 3px solid var(--border); background: #fff; padding: 20px; overflow-y: auto; }
    #reader { width: min(480px, 90%); border: 3px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-card); }
    .scan-hint { margin-top: 16px; font-weight: 700; font-size: 16px; text-align: center; }
    .start-screen { position: absolute; inset: 0; background: var(--bg); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 20px; z-index: 20; }
    .start-screen h1 { font-size: 28px; text-align: center; }
    .btn-start { border: 3px solid var(--border); border-radius: 18px; background: var(--primary); box-shadow: 6px 6px 0 var(--border); padding: 18px 40px; font-weight: 800; font-size: 20px; cursor: pointer; }
    .btn-start:active { transform: translate(3px,3px); box-shadow: 2px 2px 0 var(--border); }
    .stat-mini { border: 2px solid var(--border); border-radius: var(--radius-md); box-shadow: 3px 3px 0 var(--border); padding: 12px; text-align: center; }
    .stat-mini .v { font-size: 26px; font-weight: 800; }
    .stat-mini .l { font-size: 11px; font-weight: 700; opacity: .6; text-transform: uppercase; }
    .stat-grid-mini { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
    .last-scan-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border: 2px solid var(--border); border-radius: 12px; margin-bottom: 8px; font-size: 13px; }
    .last-scan-item.late { background: var(--yellow); }
    .last-scan-item.present { background: var(--primary-light); }
    .result-overlay {
        position: absolute; inset: 0; z-index: 30; display: none; align-items: center; justify-content: center; background: rgba(24,35,27,0.15);
    }
    .result-card { background: #fff; border: 3px solid var(--border); border-radius: var(--radius-lg); box-shadow: 8px 8px 0 var(--border); padding: 32px 40px; text-align: center; min-width: 320px; }
    .result-card img.photo { width: 96px; height: 96px; border-radius: var(--radius-md); border: 3px solid var(--border); object-fit: cover; margin-bottom: 12px; }
    .result-card .title { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
    .result-card .title.success { color: var(--primary-dark); }
    .result-card .title.late { color: #B8860B; }
    .result-card .title.error { color: #C0392B; }
    .result-card .name { font-size: 20px; font-weight: 800; }
    .result-card .meta { font-size: 14px; opacity: .7; margin-top: 4px; }
    .pin-modal { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 50; }
    .pin-box { background: #fff; border: 3px solid var(--border); border-radius: var(--radius-lg); padding: 24px; width: 280px; text-align: center; }
    #camSelect { margin-top: 10px; max-width: 300px; }
</style>
</head>
<body>

<div class="start-screen" id="startScreen">
    <div class="logo-box" style="width:64px;height:64px;font-size:24px;">SP</div>
    <h1>SISTEM PRESENSI SEKOLAH</h1>
    <p class="text-muted"><?= e($school['school_name']) ?> &middot; <?= e($device['name'] ?? '') ?></p>
    <button class="btn-start" id="btnStartKiosk">MULAI KIOSK</button>
</div>

<div class="kiosk-wrap" id="kioskScreen" style="display:none;">
    <div class="kiosk-header">
        <div class="brand" id="logoClick">
            <div class="logo-box">SP</div>
            <div>
                <div style="font-weight:800;"><?= e($school['school_name']) ?></div>
                <div class="text-sm text-muted">SISTEM PRESENSI SEKOLAH &middot; <?= e($device['name'] ?? '') ?></div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="server-status" id="serverStatus"><span class="dot"></span> <span id="serverStatusText">MENGECEK...</span></div>
            <div class="clock">
                <div class="time" id="clockTime">00:00:00</div>
                <div class="date" id="clockDate">-</div>
            </div>
        </div>
    </div>

    <div class="kiosk-body">
        <div class="kiosk-main">
            <div id="reader"></div>
            <select id="camSelect" class="form-control"></select>
            <div class="scan-hint" id="scanHint">Silakan arahkan QR Code ke kamera</div>

            <div class="result-overlay" id="resultOverlay">
                <div class="result-card" id="resultCard"></div>
            </div>
        </div>

        <div class="kiosk-side">
            <div class="stat-grid-mini">
                <div class="stat-mini" style="background:var(--primary-light);"><div class="v" id="statHadir">0</div><div class="l">Hadir Hari Ini</div></div>
                <div class="stat-mini" style="background:var(--yellow);"><div class="v" id="statTerlambat">0</div><div class="l">Terlambat</div></div>
                <div class="stat-mini" style="background:var(--blue);"><div class="v" id="statPulang">0</div><div class="l">Sudah Pulang</div></div>
                <div class="stat-mini" style="background:var(--red);"><div class="v" id="statBelum">0</div><div class="l">Belum Hadir</div></div>
            </div>
            <h4 style="margin-bottom:10px;">Scan Terakhir</h4>
            <div id="lastScans"></div>
            <label class="flex items-center gap-2" style="margin-top:10px; font-weight:600; font-size:13px;">
                <input type="checkbox" id="soundToggle" checked style="width:auto;"> Suara ON/OFF
            </label>
        </div>
    </div>
</div>

<div class="pin-modal" id="pinModal">
    <div class="pin-box">
        <h4>Masukkan PIN Kiosk</h4>
        <p class="text-sm text-muted">Untuk keluar dari mode kiosk.</p>
        <input type="password" id="exitPin" class="form-control" inputmode="numeric" style="text-align:center; letter-spacing:4px;">
        <p class="form-error" id="pinError" style="display:none;">PIN salah.</p>
        <div class="flex gap-2" style="margin-top:14px;">
            <button class="btn btn-outline w-full" id="pinCancel">Batal</button>
            <button class="btn btn-primary w-full" id="pinSubmit">Keluar</button>
        </div>
    </div>
</div>

<script>window.CSRF_TOKEN = <?= json_encode(csrf_token_value()) ?>;</script>
<script src="/assets/js/kiosk.js"></script>
</body>
</html>
