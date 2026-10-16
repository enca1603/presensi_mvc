<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Kiosk - Sistem Presensi Sekolah</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">SP</div>
        <h2>Login Kiosk</h2>
        <p class="text-sm text-muted" style="margin-bottom: 20px;">Masukkan kode perangkat dan PIN kiosk untuk memulai sesi presensi di gerbang sekolah.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/kiosk/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="device_code">Kode Kiosk</label>
                <input type="text" id="device_code" name="device_code" class="form-control" placeholder="KIOSK-001" required autofocus style="text-transform:uppercase;">
            </div>
            <div class="form-group">
                <label for="pin">PIN</label>
                <input type="password" id="pin" name="pin" class="form-control" inputmode="numeric" required>
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center; padding: 11px;">Masuk Kiosk</button>
        </form>
    </div>
</div>
</body>
</html>
