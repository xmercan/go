<?php $layout = 'layouts/panel'; ?>

<style>
.project-header {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.project-meta { font-size: .83rem; color: var(--muted); display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: .5rem; }
.project-meta span { display: flex; align-items: center; gap: .3rem; }

/* Timeline */
.timeline { position: relative; padding-left: 2.5rem; }
.timeline::before {
    content: '';
    position: absolute;
    left: 11px; top: 0; bottom: 0;
    width: 2px;
    background: var(--border);
}
.timeline-item { position: relative; margin-bottom: 1.5rem; }
.timeline-dot {
    position: absolute;
    left: -2.5rem;
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--card); border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem;
}
.timeline-dot.blue   { background: var(--blue-dim);  border-color: var(--blue); }
.timeline-dot.green  { background: rgba(34,197,94,.15); border-color: var(--green); }
.timeline-dot.orange { background: rgba(249,115,22,.15); border-color: var(--orange); }
.timeline-title { font-size: .9rem; font-weight: 600; }
.timeline-time  { font-size: .75rem; color: var(--muted); margin-top: .15rem; }
.timeline-desc  { font-size: .85rem; color: var(--muted); margin-top: .35rem; line-height: 1.6; }

/* Files table */
.file-row { display: flex; align-items: center; gap: .75rem; padding: .65rem 0; border-bottom: 1px solid var(--border); }
.file-row:last-child { border-bottom: none; }
.file-icon { font-size: 1.1rem; flex-shrink: 0; }
.file-name { font-size: .88rem; font-weight: 500; }
.file-type { font-size: .75rem; color: var(--muted); }
</style>

<!-- Header -->
<div class="project-header">
    <div>
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.4rem">
            <h1 style="font-size:1.25rem;font-weight:800"><?= e($project['name'] ?? 'Proje') ?></h1>
            <?= projectStatusBadge($project['process_status'] ?? 'draft') ?>
        </div>
        <div class="project-meta">
            <span>📅 <?= date('d.m.Y', strtotime($project['created_at'] ?? 'now')) ?></span>
            <?php if (!empty($project['sector_name'])): ?>
            <span>📂 <?= e($project['sector_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($project['budget_range'])): ?>
            <span>💰 <?= e($project['budget_range']) ?></span>
            <?php endif; ?>
            <?php if (!empty($project['urgency'])): ?>
            <span>⏰ <?= e($project['urgency']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <?php if ($project['process_status'] === 'draft'): ?>
        <a href="/chat/<?= e($project['uuid'] ?? '') ?>" class="btn btn-primary btn-sm">Analizi Tamamla →</a>
        <?php endif; ?>
        <?php if (!empty($files)): ?>
        <a href="/panel/projeler/<?= e($project['uuid'] ?? '') ?>/export" class="btn btn-outline btn-sm">📦 ZIP İndir</a>
        <?php endif; ?>
    </div>
</div>

<!-- Layout: Timeline + Files/Chat -->
<div class="grid-2" style="align-items:start">

    <!-- Timeline -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📅 Proje Zaman Çizelgesi</div>
        </div>

        <?php if (empty($activities)): ?>
        <p style="color:var(--muted);font-size:.88rem;text-align:center;padding:1.5rem 0">
            Henüz aktivite yok. Proje süreci başladığında burada görünecek.
        </p>
        <?php else: ?>
        <div class="timeline">
            <?php foreach ($activities as $act): ?>
            <div class="timeline-item">
                <div class="timeline-dot <?= e($act['color'] ?? 'blue') ?>">
                    <?= e($act['icon'] ?? '●') ?>
                </div>
                <div class="timeline-title"><?= e($act['title']) ?></div>
                <div class="timeline-time"><?= date('d.m.Y H:i', strtotime($act['created_at'])) ?></div>
                <?php if (!empty($act['description'])): ?>
                <div class="timeline-desc"><?= e($act['description']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Chat summary -->
        <?php if (!empty($project['ai_summary'])): ?>
        <div style="margin-top:1.25rem;background:var(--blue-dim);border:1px solid var(--blue-glow);border-radius:10px;padding:1rem">
            <p style="font-size:.75rem;color:var(--blue);font-weight:700;margin-bottom:.5rem">AI ÖZET</p>
            <p style="font-size:.88rem;line-height:1.7"><?= nl2br(e($project['ai_summary'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- GO! Web Files -->
    <div>
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header">
                <div class="card-title">💻 GO! Web Dosyaları</div>
            </div>

            <?php if (empty($files)): ?>
            <p style="color:var(--muted);font-size:.88rem;text-align:center;padding:1.5rem 0">
                Proje üretim aşamasına geçtiğinde dosyalar burada görünecek.
            </p>
            <?php else: ?>
            <?php foreach ($files as $f): ?>
            <div class="file-row">
                <span class="file-icon"><?= fileIcon($f['file_type'] ?? '') ?></span>
                <div style="flex:1">
                    <div class="file-name"><?= e($f['file_name']) ?></div>
                    <div class="file-type"><?= strtoupper(e($f['file_type'] ?? '')) ?></div>
                </div>
                <?php if ($f['is_previewable']): ?>
                <a href="/panel/projeler/<?= e($project['uuid']) ?>/preview/<?= (int)$f['id'] ?>"
                   class="btn btn-outline btn-sm" target="_blank">Önizle</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
                <a href="/panel/projeler/<?= e($project['uuid']) ?>/export" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">
                    📦 Tüm Dosyaları ZIP İndir
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Project details card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">📝 Proje Detayları</div>
            </div>
            <?php $details = [
                'Hedef Kitle'    => $project['target_audience'] ?? '',
                'Marka Durumu'   => $project['brand_type'] === 'existing' ? 'Mevcut marka' : 'Yeni marka',
                'Domain İhtiyacı'=> $project['has_domain'] ? 'Mevcut domain var' : 'Yeni domain gerekli',
                'Hosting'        => $project['hosting_need'] ?? '',
                'Sosyal Medya'   => $project['social_media_needs'] ?? '',
                'Notlar'         => $project['important_notes'] ?? '',
            ]; ?>
            <?php foreach ($details as $label => $val): ?>
            <?php if (!empty($val)): ?>
            <div style="padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.88rem;display:flex;gap:.75rem">
                <span style="color:var(--muted);min-width:130px;flex-shrink:0"><?= e($label) ?></span>
                <span><?= e($val) ?></span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
function projectStatusBadge(string $s): string {
    $m = ['draft'=>['gray','Taslak'],'queued'=>['blue','Sırada'],'reviewing'=>['orange','İnceleniyor'],'in_progress'=>['blue','Üretimde'],'completed'=>['green','Tamamlandı'],'cancelled'=>['red','İptal']];
    [$c,$l] = $m[$s] ?? ['gray',ucfirst($s)];
    return "<span class=\"badge badge-{$c}\">{$l}</span>";
}
function fileIcon(string $type): string {
    return match($type) { 'html'=>'🌐','css'=>'🎨','js'=>'⚡','php'=>'🐘','json'=>'📦','txt'=>'📄', default=>'📁' };
}
?>
