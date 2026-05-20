<?php $layout = 'layouts/panel'; ?>

<!-- Stat cards -->
<div class="grid-4" style="margin-bottom:1.75rem">
    <div class="stat-card">
        <div class="stat-label">Toplam Proje</div>
        <div class="stat-value text-blue"><?= (int)($totalProjects ?? 0) ?></div>
        <div class="stat-sub">Tüm projeler</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Aktif</div>
        <div class="stat-value text-orange"><?= (int)($activeCount ?? 0) ?></div>
        <div class="stat-sub">Devam eden</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Tamamlanan</div>
        <div class="stat-value text-green"><?= (int)($doneCount ?? 0) ?></div>
        <div class="stat-sub">Başarıyla teslim</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Destek</div>
        <div class="stat-value">7/24</div>
        <div class="stat-sub"><a href="/panel/destek" style="color:var(--blue);text-decoration:none">Ticket Aç →</a></div>
    </div>
</div>

<!-- GO! Chat CTA -->
<?php if (empty($myProjects)): ?>
<div style="background:linear-gradient(135deg,rgba(0,212,255,.1),rgba(34,197,94,.06));border:1px solid rgba(0,212,255,.2);border-radius:16px;padding:2.5rem;text-align:center;margin-bottom:1.75rem">
    <div style="font-size:3rem;margin-bottom:1rem">💬</div>
    <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:.75rem">İlk projenizi başlatın</h2>
    <p style="color:var(--muted);margin-bottom:1.5rem;max-width:440px;margin-inline:auto">
        GO! Chat ile ihtiyaçlarınızı anlatalım, size özel bir dijital strateji hazırlayalım.
    </p>
    <a href="/chat" class="btn btn-primary btn-lg">GO! Chat Başlat →</a>
</div>
<?php elseif ($hasDraft ?? false): ?>
<div style="background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.2);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.75rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
    <div>
        <strong>⚠️ Tamamlanmamış proje analizi</strong>
        <p style="font-size:.85rem;color:var(--muted);margin-top:.25rem">GO! Chat sohbetiniz tamamlanmadı. Kaldığınız yerden devam edin.</p>
    </div>
    <a href="/chat" class="btn btn-primary btn-sm">Devam Et →</a>
</div>
<?php endif; ?>

<!-- Recent projects -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Son Projeler</div>
        <a href="/panel/projeler" class="btn btn-outline btn-sm">Tümünü Gör</a>
    </div>

    <?php if (empty($myProjects)): ?>
    <p style="color:var(--muted);font-size:.9rem;text-align:center;padding:2rem 0">
        Henüz projeniz yok. <a href="/chat" style="color:var(--blue)">GO! Chat ile başlatın →</a>
    </p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Proje</th>
                    <th>Sektör</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myProjects as $p): ?>
                <tr>
                    <td>
                        <a href="/panel/projeler/<?= e($p['uuid'] ?? '') ?>" style="color:var(--text);text-decoration:none;font-weight:600">
                            <?= e($p['name'] ?? 'Proje') ?>
                        </a>
                    </td>
                    <td style="color:var(--muted);font-size:.85rem"><?= e($p['sector_name'] ?? '—') ?></td>
                    <td><?= statusBadge($p['process_status'] ?? 'draft') ?></td>
                    <td style="color:var(--muted);font-size:.82rem"><?= date('d.m.Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <a href="/panel/projeler/<?= e($p['uuid'] ?? '') ?>" class="btn btn-outline btn-sm">Detay</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php
function statusBadge(string $status): string {
    $map = [
        'draft'       => ['gray',   'Taslak'],
        'queued'      => ['blue',   'Sırada'],
        'reviewing'   => ['orange', 'İnceleniyor'],
        'in_progress' => ['blue',   'Üretimde'],
        'completed'   => ['green',  'Tamamlandı'],
        'cancelled'   => ['red',    'İptal'],
    ];
    [$color, $label] = $map[$status] ?? ['gray', ucfirst($status)];
    return "<span class=\"badge badge-{$color}\">{$label}</span>";
}
?>
