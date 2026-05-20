<?php $layout = 'layouts/panel'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h2 style="font-size:1.1rem;font-weight:800">🌐 Domain Yönetimi</h2>
        <p style="font-size:.85rem;color:var(--muted);margin-top:.2rem">Domainleriniz ve işlem talepleriniz</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('newRequestModal').style.display='flex'">
        + Yeni Talep
    </button>
</div>

<!-- Domains -->
<div class="card" style="margin-bottom:1.25rem">
    <div class="card-header"><div class="card-title">Domainlerim</div></div>
    <?php if (empty($domains)): ?>
    <p style="color:var(--muted);font-size:.88rem;padding:1rem 0">Henüz domain kaydınız bulunmuyor.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Domain</th><th>Kayıt Tarihi</th><th>Bitiş Tarihi</th><th>Durum</th></tr></thead>
            <tbody>
                <?php foreach ($domains as $d): ?>
                <tr>
                    <td><strong><?= e($d['domain_name']) ?></strong></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= $d['registered_at'] ? date('d.m.Y', strtotime($d['registered_at'])) : '—' ?></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= $d['expires_at'] ? date('d.m.Y', strtotime($d['expires_at'])) : '—' ?></td>
                    <td><?= domainStatusBadge($d['status'] ?? 'pending') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Requests -->
<div class="card">
    <div class="card-header"><div class="card-title">Taleplerim</div></div>
    <?php if (empty($requests)): ?>
    <p style="color:var(--muted);font-size:.88rem;padding:1rem 0">Henüz talebiniz yok.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tür</th><th>Durum</th><th>Tarih</th></tr></thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= e(requestTypeLabel($r['request_type'])) ?></td>
                    <td><?= kanbanBadge($r['kanban_status'] ?? 'pending') ?></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="newRequestModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:500;align-items:center;justify-content:center;padding:1rem">
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:2rem;width:100%;max-width:500px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <strong>Yeni Domain Talebi</strong>
            <button onclick="document.getElementById('newRequestModal').style.display='none'" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem">✕</button>
        </div>
        <form method="POST" action="/panel/domain/talep">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Talep Türü</label>
                <select name="request_type" required>
                    <option value="ns_change">Nameserver Değişikliği</option>
                    <option value="dns_add">DNS Ekleme</option>
                    <option value="dns_delete">DNS Silme</option>
                    <option value="transfer_code">Transfer Kodu</option>
                    <option value="internal_transfer">İç Transfer</option>
                    <option value="renewal">Yenileme</option>
                    <option value="other">Diğer</option>
                </select>
            </div>
            <?php if (!empty($domains)): ?>
            <div class="form-group">
                <label>Domain (opsiyonel)</label>
                <select name="domain_id">
                    <option value="">Seçin...</option>
                    <?php foreach ($domains as $d): ?>
                    <option value="<?= (int)$d['id'] ?>"><?= e($d['domain_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Mesaj / Detay</label>
                <textarea name="message" rows="3" placeholder="Talebinizi açıklayın..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Talep Gönder</button>
        </form>
    </div>
</div>

<?php
function domainStatusBadge(string $s): string {
    $m = ['active'=>'green','pending'=>'blue','expired'=>'red','transferred'=>'gray'];
    $l = ['active'=>'Aktif','pending'=>'Bekliyor','expired'=>'Süresi Doldu','transferred'=>'Transfer'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
function kanbanBadge(string $s): string {
    $m = ['pending'=>'gray','reviewing'=>'orange','in_progress'=>'blue','completed'=>'green','rejected'=>'red'];
    $l = ['pending'=>'Bekliyor','reviewing'=>'İnceleniyor','in_progress'=>'İşlemde','completed'=>'Tamamlandı','rejected'=>'Reddedildi'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
function requestTypeLabel(string $t): string {
    $m = ['ns_change'=>'NS Değişikliği','dns_add'=>'DNS Ekleme','dns_delete'=>'DNS Silme','transfer_code'=>'Transfer Kodu','internal_transfer'=>'İç Transfer','renewal'=>'Yenileme','ftp_info'=>'FTP Bilgisi','cpanel_info'=>'cPanel Bilgisi','backup'=>'Yedek','upgrade'=>'Yükseltme','install'=>'Kurulum','update'=>'Güncelleme','support'=>'Destek','other'=>'Diğer'];
    return $m[$t] ?? ucfirst($t);
}
?>
