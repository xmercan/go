<?php $layout = 'layouts/admin'; ?>

<!-- Stats grid -->
<div class="grid-4" style="margin-bottom:1.75rem">
    <div class="stat-card">
        <div class="stat-label">Toplam Kullanıcı</div>
        <div class="stat-value text-blue"><?= number_format($stats['total_users'] ?? 0) ?></div>
        <div class="stat-sub">+<?= $stats['new_users_7d'] ?? 0 ?> bu hafta</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Toplam Proje</div>
        <div class="stat-value text-orange"><?= number_format($stats['total_projects'] ?? 0) ?></div>
        <div class="stat-sub">
            <?= $stats['projects_by_status']['queued'] ?? 0 ?> sırada,
            <?= $stats['projects_by_status']['in_progress'] ?? 0 ?> üretimde
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Toplam Gelir</div>
        <div class="stat-value text-green"><?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>₺</div>
        <div class="stat-sub"><?= $stats['total_invoices'] ?? 0 ?> fatura</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Açık Destek</div>
        <div class="stat-value text-red"><?= $stats['open_tickets'] ?? 0 ?></div>
        <div class="stat-sub">
            <?= $stats['pending_domain'] ?? 0 ?> domain,
            <?= $stats['pending_hosting'] ?? 0 ?> hosting
        </div>
    </div>
</div>

<!-- Queued Projects -->
<div class="grid-2" style="align-items:start">
    <div class="card">
        <div class="card-header">
            <div class="card-title">📁 Sıradaki Projeler</div>
            <a href="/admin/projeler" class="btn btn-outline btn-sm">Tümü</a>
        </div>

        <?php if (empty($recentProjects)): ?>
        <p style="color:var(--muted);font-size:.88rem;text-align:center;padding:2rem 0">Sırada proje yok.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Proje</th><th>Müşteri</th><th>Sektör</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($recentProjects as $p): ?>
                    <tr>
                        <td><strong><?= e(mb_strimwidth($p['name'] ?? '', 0, 30, '...')) ?></strong></td>
                        <td style="font-size:.8rem;color:var(--muted)"><?= e($p['user_name'] ?? '—') ?></td>
                        <td style="font-size:.8rem;color:var(--muted)"><?= e($p['sector_name'] ?? '—') ?></td>
                        <td>
                            <a href="/admin/projeler/<?= e($p['uuid'] ?? '') ?>" class="btn btn-outline btn-sm">Detay</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pending invoices -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">🧾 Onay Bekleyen Ödemeler</div>
            <a href="/admin/faturalar" class="btn btn-outline btn-sm">Tümü</a>
        </div>

        <?php if (empty($pendingInvoices)): ?>
        <p style="color:var(--muted);font-size:.88rem;text-align:center;padding:2rem 0">Bekleyen ödeme yok.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Fatura</th><th>Müşteri</th><th>Tutar</th><th>Durum</th></tr></thead>
                <tbody>
                    <?php foreach ($pendingInvoices as $inv): ?>
                    <tr>
                        <td>
                            <a href="/admin/faturalar/<?= e($inv['uuid']) ?>" style="color:var(--blue);text-decoration:none">
                                #<?= e($inv['invoice_no']) ?>
                            </a>
                        </td>
                        <td style="font-size:.8rem;color:var(--muted)"><?= e($inv['user_name'] ?? '—') ?></td>
                        <td><strong><?= number_format((float)$inv['total'], 0, ',', '.') ?>₺</strong></td>
                        <td><?= adminInvBadge($inv['status'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick links -->
<div class="grid-4" style="margin-top:1.5rem">
    <?php $quickLinks = [
        ['/admin/kanban','🗂️','Kanban','Talepleri yönet'],
        ['/admin/destek','🎯','Destek','Ticket\'lara yanıt ver'],
        ['/admin/loglar','📋','Loglar','Sistem aktivitesi'],
        ['/admin/ayarlar','⚙️','Ayarlar','Site yapılandırması'],
    ]; ?>
    <?php foreach ($quickLinks as [$href, $icon, $title, $desc]): ?>
    <a href="<?= $href ?>" style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.25rem;display:block;text-decoration:none;transition:background .2s" onmouseover="this.style.background='var(--card-h)'" onmouseout="this.style.background='var(--card)'">
        <div style="font-size:1.5rem;margin-bottom:.5rem"><?= $icon ?></div>
        <div style="font-size:.9rem;font-weight:700;color:var(--text)"><?= $title ?></div>
        <div style="font-size:.78rem;color:var(--muted);margin-top:.2rem"><?= $desc ?></div>
    </a>
    <?php endforeach; ?>
</div>

<?php
function adminInvBadge(string $s): string {
    $m = ['waiting'=>'orange','payment_pending'=>'blue','payment_reported'=>'orange','paid'=>'green'];
    $l = ['waiting'=>'Bekliyor','payment_pending'=>'Bekliyor','payment_reported'=>'Bildirim Var','paid'=>'Ödendi'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
?>
