<?php $layout = 'layouts/panel'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h2 style="font-size:1.15rem;font-weight:800">Projelerim</h2>
        <p style="font-size:.85rem;color:var(--muted);margin-top:.2rem"><?= count($myProjects ?? []) ?> proje</p>
    </div>
    <a href="/chat" class="btn btn-primary btn-sm">+ Yeni Proje</a>
</div>

<?php if (empty($myProjects)): ?>
<div style="text-align:center;padding:4rem 0">
    <div style="font-size:3rem;margin-bottom:1rem">📁</div>
    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem">Henüz projeniz yok</h3>
    <p style="color:var(--muted);margin-bottom:1.5rem">GO! Chat ile ilk projenizi başlatın.</p>
    <a href="/chat" class="btn btn-primary">GO! Chat ile Başla →</a>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:1rem">
    <?php foreach ($myProjects as $p): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.35rem">
                <a href="/panel/projeler/<?= e($p['uuid'] ?? '') ?>" style="font-size:1rem;font-weight:700;color:var(--text);text-decoration:none">
                    <?= e($p['name'] ?? 'Proje') ?>
                </a>
                <?= pStatusBadge($p['process_status'] ?? 'draft') ?>
            </div>
            <div style="font-size:.8rem;color:var(--muted);display:flex;gap:1rem;flex-wrap:wrap">
                <?php if (!empty($p['sector_name'])): ?>
                <span>📂 <?= e($p['sector_name']) ?></span>
                <?php endif; ?>
                <span>📅 <?= date('d.m.Y', strtotime($p['created_at'])) ?></span>
                <?php if (!empty($p['budget_range'])): ?>
                <span>💰 <?= e($p['budget_range']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex;gap:.5rem">
            <?php if ($p['process_status'] === 'draft'): ?>
            <a href="/chat/<?= e($p['uuid'] ?? '') ?>" class="btn btn-primary btn-sm">Tamamla</a>
            <?php endif; ?>
            <a href="/panel/projeler/<?= e($p['uuid'] ?? '') ?>" class="btn btn-outline btn-sm">Detay</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
function pStatusBadge(string $s): string {
    $m = ['draft'=>['gray','Taslak'],'queued'=>['blue','Sırada'],'reviewing'=>['orange','İnceleniyor'],'in_progress'=>['blue','Üretimde'],'completed'=>['green','Tamamlandı'],'cancelled'=>['red','İptal']];
    [$c,$l] = $m[$s] ?? ['gray',ucfirst($s)];
    return "<span class=\"badge badge-{$c}\">{$l}</span>";
}
?>
