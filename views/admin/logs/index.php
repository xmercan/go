<?php $layout = 'layouts/admin'; ?>

<div class="settings-tabs" style="margin-bottom:1.5rem">
    <?php foreach (['activity'=>'📋 Aktivite','login'=>'🔐 Giriş Logları','audit'=>'🔒 Audit Trail','email'=>'📧 E-posta Logları'] as $t => $l): ?>
    <a href="/admin/loglar?tab=<?= $t ?>" class="settings-tab <?= $tab === $t ? 'active' : '' ?>" style="text-decoration:none"><?= $l ?></a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <?= (int)$total ?> kayıt — Sayfa <?= $page ?>/<?= max(1, ceil($total / $perPage)) ?>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <?php if ($tab === 'activity'): ?>
            <thead><tr><th>Aktör</th><th>Eylem</th><th>Nesne</th><th>IP</th><th>Tarih</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['user_name'] ?? $log['admin_name'] ?? 'Sistem') ?></td>
                    <td><span class="badge badge-blue"><?= e($log['action']) ?></span></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= e($log['entity_type']) ?> #<?= e($log['entity_id'] ?? '') ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= e($log['ip_address']) ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <?php elseif ($tab === 'login'): ?>
            <thead><tr><th>E-posta</th><th>Tür</th><th>Durum</th><th>IP</th><th>Tarih</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['email_attempt']) ?></td>
                    <td><?= e($log['actor_type']) ?></td>
                    <td>
                        <span class="badge badge-<?= $log['status'] === 'success' ? 'green' : 'red' ?>">
                            <?= $log['status'] === 'success' ? '✓ Başarılı' : '✗ Başarısız' ?>
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= e($log['ip_address']) ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <?php elseif ($tab === 'audit'): ?>
            <thead><tr><th>Admin</th><th>Eylem</th><th>Nesne</th><th>Tarih</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['actor_name'] ?? 'Sistem') ?></td>
                    <td><span class="badge badge-orange"><?= e($log['action']) ?></span></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= e($log['entity_type']) ?> #<?= e($log['entity_id'] ?? '') ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

            <?php else: // email ?>
            <thead><tr><th>Alıcı</th><th>Konu</th><th>Template</th><th>Durum</th><th>Tarih</th></tr></thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['to_email']) ?></td>
                    <td style="font-size:.83rem"><?= e(mb_strimwidth($log['subject'], 0, 50, '...')) ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= e($log['template_key']) ?></td>
                    <td>
                        <?php $sc = ['queued'=>'gray','sent'=>'green','failed'=>'red']; ?>
                        <span class="badge badge-<?= $sc[$log['status']] ?? 'gray' ?>"><?= e($log['status']) ?></span>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php endif; ?>
        </table>
    </div>

    <!-- Pagination -->
    <?php $totalPages = max(1, (int)ceil($total / $perPage)); if ($totalPages > 1): ?>
    <div style="display:flex;gap:.4rem;justify-content:center;margin-top:1.25rem;flex-wrap:wrap">
        <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
        <a href="?tab=<?= $tab ?>&page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.settings-tab{text-decoration:none;}
.settings-tab.active{background:var(--blue);color:#0A1628;}
.settings-tabs{display:flex;gap:.4rem;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:.35rem;width:fit-content;flex-wrap:wrap;}
.settings-tab{padding:.4rem .85rem;border-radius:6px;font-size:.83rem;font-weight:600;color:var(--muted);transition:background .2s;}
.settings-tab:hover{color:var(--text);background:var(--card-h);}
</style>
