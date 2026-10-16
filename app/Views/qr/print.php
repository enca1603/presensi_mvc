<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak QR Code</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; padding: 24px; background: #F7FBF8; color: #18231B; }
    .toolbar { margin-bottom: 20px; }
    .toolbar button {
        border: 2px solid #18231B; border-radius: 12px; background: #B8E6C1; padding: 10px 20px;
        font-weight: 700; cursor: pointer; box-shadow: 3px 3px 0 #18231B;
    }
    .sheet { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .qr-card {
        border: 2px solid #18231B; border-radius: 18px; box-shadow: 4px 4px 0 #18231B;
        padding: 16px; text-align: center; background: #fff; break-inside: avoid; page-break-inside: avoid;
    }
    .qr-card img { width: 100%; max-width: 180px; height: auto; margin: 8px 0; }
    .qr-card .name { font-weight: 800; font-size: 14px; margin-top: 6px; }
    .qr-card .code { font-size: 12px; color: #555; }
    .qr-card .sub { font-size: 11px; color: #79B989; font-weight: 700; }
    @media print {
        .toolbar { display: none; }
        body { padding: 0; background: #fff; }
        .qr-card { box-shadow: none; }
    }
</style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Cetak Sekarang</button>
    </div>
    <div class="sheet">
        <?php foreach ($items as $item): ?>
            <div class="qr-card">
                <img src="<?= e($item['image']) ?>" alt="QR Code">
                <div class="name"><?= e($item['name']) ?></div>
                <div class="code"><?= e($item['code']) ?></div>
                <div class="sub"><?= e($item['sub']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
