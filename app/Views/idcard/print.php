<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= e($title) ?></title>
<style>
    @page { size: 85.60mm 53.98mm; margin: 0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #ddd; }
    .toolbar { padding: 12px; background: #fff; text-align: center; }
    .toolbar button {
        border: 2px solid #18231B; border-radius: 12px; background: #B8E6C1; padding: 10px 24px;
        font-weight: 700; cursor: pointer; box-shadow: 3px 3px 0 #18231B;
    }
    .card-page {
        width: 85.60mm; height: 53.98mm;
        page-break-after: always; break-inside: avoid;
        position: relative; overflow: hidden;
        margin: 10px auto; background: #F7FBF8;
        border: 1.2mm solid #18231B; border-radius: 3mm;
        box-shadow: 1.5mm 1.5mm 0 #18231B;
    }
    .card-front { padding: 3mm; display: flex; flex-direction: column; height: 100%; }
    .card-front .head { display: flex; align-items: center; gap: 2mm; border-bottom: 0.5mm solid #18231B; padding-bottom: 1.5mm; margin-bottom: 2mm; }
    .card-front .logo { width: 6mm; height: 6mm; background: #B8E6C1; border: 0.4mm solid #18231B; border-radius: 1.2mm; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 3mm; font-weight: 800; }
    .card-front .school-name { font-size: 2.6mm; font-weight: 800; line-height: 1.1; }
    .card-front .card-title { font-size: 2mm; font-weight: 700; color: #79B989; letter-spacing: 0.3mm; }
    .card-front .body { display: flex; gap: 3mm; flex: 1; align-items: center; }
    .card-front .photo { width: 16mm; height: 20mm; border: 0.5mm solid #18231B; border-radius: 1.5mm; object-fit: cover; background: #eee; flex-shrink: 0; }
    .card-front .info .name { font-size: 3.4mm; font-weight: 800; line-height: 1.2; margin-bottom: 1mm; }
    .card-front .info .code { font-size: 2.6mm; font-weight: 600; }
    .card-front .info .sub { font-size: 2.4mm; font-weight: 700; color: #18231B; background: #CDE5FF; display: inline-block; padding: 0.3mm 1.5mm; border-radius: 1mm; border: 0.3mm solid #18231B; margin-top: 1mm; }

    .card-back { padding: 3mm; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; }
    .card-back .school-name { font-size: 2.6mm; font-weight: 800; margin-bottom: 1.5mm; }
    .card-back img.qr { width: 28mm; height: 28mm; background: #fff; padding: 1mm; border: 0.4mm solid #18231B; border-radius: 1.5mm; }
    .card-back .name { font-size: 2.6mm; font-weight: 700; margin-top: 1.5mm; }
    .card-back .code { font-size: 2.2mm; }
    .card-back .presensi-label { font-size: 2mm; font-weight: 800; color: #79B989; margin-top: 1mm; letter-spacing: 0.3mm; }
    .card-back .hint { font-size: 1.8mm; color: #555; margin-top: 1mm; max-width: 60mm; }

    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .card-page { margin: 0; box-shadow: none; border-radius: 0; }
    }
</style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Cetak Sekarang</button></div>

    <?php foreach ($items as $item): ?>
        <div class="card-page card-front">
            <div class="head">
                <div class="logo">SP</div>
                <div>
                    <div class="school-name"><?= e($school['school_name']) ?></div>
                    <div class="card-title">KARTU IDENTITAS <?= $item['type'] === 'student' ? 'SISWA' : 'GURU' ?></div>
                </div>
            </div>
            <div class="body">
                <img class="photo" src="<?= e($item['photo'] ?: 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($item['name'])) ?>" alt="">
                <div class="info">
                    <div class="name"><?= e($item['name']) ?></div>
                    <div class="code"><?= e($item['code_label']) ?> <?= e($item['code']) ?></div>
                    <div class="sub"><?= e($item['sub']) ?></div>
                </div>
            </div>
        </div>

        <div class="card-page card-back">
            <div class="school-name"><?= e($school['school_name']) ?></div>
            <img class="qr" src="<?= e($item['qr_image']) ?>" alt="QR Code">
            <div class="name"><?= e($item['name']) ?></div>
            <div class="code"><?= e($item['code_label']) ?> <?= e($item['code']) ?></div>
            <div class="presensi-label">KARTU PRESENSI</div>
            <div class="hint">Gunakan QR Code ini untuk melakukan presensi.</div>
        </div>
    <?php endforeach; ?>
</body>
</html>
