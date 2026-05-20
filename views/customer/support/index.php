<?php $layout = 'layouts/panel'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h2 style="font-size:1.1rem;font-weight:800">🎯 Destek Talepleri</h2>
        <p style="font-size:.85rem;color:var(--muted);margin-top:.2rem"><?= count($tickets ?? []) ?> ticket</p>
    </div>
    <a href="/panel/destek/yeni" class="btn btn-primary btn-sm">+ Yeni Talep</a>
</div>

<?php if (empty($tickets)): ?>
<div style="text-align:center;padding:4rem 0">
    <div style="font-size:3rem;margin-bottom:1rem">🎯</div>
    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem">Henüz destek talebiniz yok</h3>
    <p style="color:var(--muted);margin-bottom:1.5rem">Bir sorun mu yaşıyorsunuz? Yeni bir destek talebi açın.</p>
    <a href="/panel/destek/yeni" class="btn btn-primary">Yeni Talep Aç</a>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Konu</th><th>Kategori</th><th>Öncelik</th><th>Durum</th><th>Tarih</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>
                        <a href="/panel/destek/<?= e($t['uuid']) ?>" style="color:var(--text);text-decoration:none;font-weight:500">
                            <?= e(mb_strimwidth($t['subject'], 0, 55, '...')) ?>
                        </a>
                    </td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= e($t['category']) ?></td>
                    <td><?= priorityBadge($t['priority'] ?? 'normal') ?></td>
                    <td><?= ticketStatusBadge($t['status'] ?? 'open') ?></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= date('d.m.Y', strtotime($t['updated_at'])) ?></td>
                    <td><a href="/panel/destek/<?= e($t['uuid']) ?>" class="btn btn-outline btn-sm">Görüntüle</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
function ticketStatusBadge(string $s): string {
    $m = ['open'=>'blue','answered'=>'green','waiting_customer'=>'orange','closed'=>'gray'];
    $l = ['open'=>'Açık','answered'=>'Yanıtlandı','waiting_customer'=>'Yanıt Bekleniyor','closed'=>'Kapalı'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
function priorityBadge(string $p): string {
    $m = ['low'=>'gray','normal'=>'blue','high'=>'orange','urgent'=>'red'];
    $l = ['low'=>'Düşük','normal'=>'Normal','high'=>'Yüksek','urgent'=>'Acil'];
    return '<span class="badge badge-'.($m[$p]??'gray').'">'.($l[$p]??ucfirst($p)).'</span>';
}
?>
