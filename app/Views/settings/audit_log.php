<div class="card">
    <div class="card-header"><h3>Audit Log</h3></div>

    <form method="GET" action="/settings/audit-log" class="flex gap-3" style="margin-bottom:18px;">
        <select name="module" class="form-control" style="max-width:200px;">
            <option value="">Semua Modul</option>
            <?php foreach ($modules as $m): ?>
                <option value="<?= e($m) ?>" <?= $module === $m ? 'selected' : '' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Modul</th><th>Deskripsi</th><th>IP</th></tr></thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="text-muted" style="text-align:center; padding:24px;">Belum ada aktivitas tercatat.</td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-sm text-muted"><?= format_date($log['created_at'], 'd/m/Y H:i:s') ?></td>
                    <td><?= e($log['user_name'] ?? 'Sistem') ?></td>
                    <td><span class="badge badge-blue"><?= e($log['action']) ?></span></td>
                    <td><?= e($log['module']) ?></td>
                    <td class="text-sm"><?= e($log['description']) ?></td>
                    <td class="text-sm text-muted"><?= e($log['ip_address']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php for ($p = 1; $p <= $lastPage; $p++): ?>
            <a href="/settings/audit-log?<?= http_build_query(array_filter(['module' => $module, 'page' => $p])) ?>" class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
</div>
