<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Dashboard') ?> - <?= e(config('app.name')) ?></title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
</head>
<body>
<div class="app-shell">
    <?php \App\Core\View::partial('components.sidebar', ['currentUser' => $currentUser]); ?>

    <div class="main-content">
        <div class="topbar">
            <button class="btn btn-sm" id="sidebarToggle" style="display:none;" aria-label="Menu">
                <i data-lucide="menu"></i>
            </button>
            <h3 class="mb-0"><?= e($title ?? 'Dashboard') ?></h3>
            <div class="flex items-center gap-3">
                <div class="user-chip">
                    <i data-lucide="user-circle"></i>
                    <span><?= e($currentUser['full_name'] ?? '-') ?></span>
                    <span class="badge badge-green" style="margin-left:4px;"><?= e($currentUser['role_name'] ?? '-') ?></span>
                </div>
                <form method="POST" action="/logout">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-red" aria-label="Logout"><i data-lucide="log-out"></i></button>
                </form>
            </div>
        </div>

        <div class="page-body">
            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('error')): ?>
                <div class="alert alert-error"><?= e($msg) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>
</div>
<script>
    if (window.lucide) { lucide.createIcons(); }
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });
</script>
</body>
</html>
