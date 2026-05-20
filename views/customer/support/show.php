<?php $layout = 'layouts/panel'; ?>

<div style="margin-bottom:1.25rem">
    <a href="/panel/destek" style="font-size:.85rem;color:var(--muted);text-decoration:none">← Destek Talepleri</a>
</div>

<div class="card" style="margin-bottom:1.25rem">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem">
        <div>
            <h2 style="font-size:1.05rem;font-weight:800"><?= e($ticket['subject'] ?? '') ?></h2>
            <div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;display:flex;gap:1rem;flex-wrap:wrap">
                <span>Kategori: <?= e($ticket['category'] ?? '') ?></span>
                <span>Tarih: <?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></span>
            </div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <?= tsBadge($ticket['priority'] ?? 'normal') ?>
            <?= tsStatusBadge($ticket['status'] ?? 'open') ?>
        </div>
    </div>
</div>

<!-- Replies -->
<div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.25rem">
    <?php foreach ($replies ?? [] as $reply): ?>
    <?php $isUser = $reply['author_type'] === 'user'; ?>
    <div style="display:flex;gap:.75rem;<?= $isUser ? 'flex-direction:row-reverse' : '' ?>">
        <div style="width:36px;height:36px;border-radius:50%;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0">
            <?= $isUser ? '👤' : '👨‍💼' ?>
        </div>
        <div style="flex:1;max-width:80%">
            <div style="font-size:.78rem;color:var(--muted);margin-bottom:.3rem;<?= $isUser ? 'text-align:right' : '' ?>">
                <strong style="color:var(--text)"><?= e($reply['author_name'] ?? ($isUser ? 'Siz' : 'GO! Destek')) ?></strong>
                — <?= date('d.m.Y H:i', strtotime($reply['created_at'])) ?>
            </div>
            <div style="background:<?= $isUser ? 'var(--blue-dim)' : 'var(--card)' ?>;border:1px solid <?= $isUser ? 'var(--blue-glow)' : 'var(--border)' ?>;border-radius:<?= $isUser ? '12px 4px 12px 12px' : '4px 12px 12px 12px' ?>;padding:.85rem 1rem;font-size:.9rem;line-height:1.6">
                <?= nl2br(e($reply['message'])) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Reply form -->
<?php if (($ticket['status'] ?? '') !== 'closed'): ?>
<div class="card">
    <div class="card-title" style="margin-bottom:1rem">Yanıt Gönder</div>
    <form method="POST" action="/panel/destek/<?= e($ticket['uuid'] ?? '') ?>/yanit">
        <?= csrf_field() ?>
        <div class="form-group">
            <textarea name="message" rows="4" placeholder="Yanıtınızı yazın..." required style="min-height:100px"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Yanıt Gönder</button>
    </form>
</div>
<?php else: ?>
<div style="background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:10px;padding:1rem;text-align:center;color:var(--muted);font-size:.9rem">
    Bu ticket kapatılmıştır.
</div>
<?php endif; ?>

<?php
function tsBadge(string $p): string {
    $m = ['low'=>'gray','normal'=>'blue','high'=>'orange','urgent'=>'red'];
    $l = ['low'=>'Düşük','normal'=>'Normal','high'=>'Yüksek','urgent'=>'Acil'];
    return '<span class="badge badge-'.($m[$p]??'gray').'">'.($l[$p]??ucfirst($p)).'</span>';
}
function tsStatusBadge(string $s): string {
    $m = ['open'=>'blue','answered'=>'green','waiting_customer'=>'orange','closed'=>'gray'];
    $l = ['open'=>'Açık','answered'=>'Yanıtlandı','waiting_customer'=>'Yanıt Bekleniyor','closed'=>'Kapalı'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
?>
