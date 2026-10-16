<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">SP</div>
        <h2>Sistem Presensi Sekolah</h2>
        <p class="text-sm text-muted" style="margin-bottom: 20px;">Masuk untuk mengelola presensi sekolah</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center; padding: 11px;">Masuk</button>
        </form>
    </div>
</div>
