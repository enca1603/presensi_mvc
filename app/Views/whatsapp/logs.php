<div class="card">
    <div class="card-header"><h3>Log WhatsApp</h3></div>

    <form method="GET" action="/whatsapp/logs" class="flex gap-3" style="margin-bottom:18px;">
        <select name="status" class="form-control" style="max-width:180px;">
            <option value="">Semua Status</option>
            <?php foreach (['pending' => 'Pending', 'sent' => 'Terkirim', 'failed' => 'Gagal'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-outline" type="submit"><i data-lucide="search"></i> Filter</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Waktu</th><th>Nomor</th><th>Pesan</th><th>Status</th><th>Percobaan</th><th>Respons</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="7" class="text-muted" style="text-align:center; padding:24px;">Belum ada log WhatsApp.</td></tr>
            <?php endif; ?>
            <?php $statusMap = ['pending' => 'badge-yellow|Pending', 'sent' => 'badge-green|Terkirim', 'failed' => 'badge-red|Gagal']; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-sm text-muted"><?= format_date($log['created_at'], 'd/m/Y H:i') ?></td>
                    <td><?= e($log['phone_number']) ?></td>
                    <td class="text-sm" style="max-width:260px; white-space:pre-line;"><?= e(mb_strimwidth($log['message'], 0, 80, '...')) ?></td>
                    <td><?php [$cls, $label] = explode('|', $statusMap[$log['status']] ?? 'badge-gray|-'); ?><span class="badge <?= $cls ?>"><?= $label ?></span></td>
                    <td><?= (int) $log['attempt_count'] ?></td>
                    <td class="text-sm text-muted" style="max-width:200px;"><?= e(mb_strimwidth((string) $log['provider_response'], 0, 60, '...')) ?></td>
                    <td>
                        <?php if ($log['status'] !== 'sent'): ?>
                        <form method="POST" action="/whatsapp/logs/<?= (int) $log['id'] ?>/retry">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-yellow"><i data-lucide="rotate-cw"></i> Retry</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
