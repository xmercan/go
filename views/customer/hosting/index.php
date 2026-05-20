<?php $layout = 'layouts/panel'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
    <div>
        <h2 style="font-size:1.1rem;font-weight:800">🖥️ Hosting Yönetimi</h2>
        <p style="font-size:.85rem;color:var(--muted);margin-top:.2rem">Hosting hizmetleriniz ve talepleriniz</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('newRequestModal').style.display='flex'">
        + Yeni Talep
    </button>
</div>

<!-- Services -->
<div class="card" style="margin-bottom:1.25rem">
    <div class="card-header"><div class="card-title">Hosting Hizmetlerim</div></div>
    <?php if (empty($services)): ?>
    <p style="color:var(--muted);font-size:.88rem;padding:1rem 0">Aktif hosting hizmetiniz bulunmuyor.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Paket</th><th>Disk</th><th>cPanel</th><th>Durum</th></tr></thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td><strong><?= e($s['package_name'] ?? '—') ?></strong></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= e($s['disk_quota'] ?? '—') ?></td>
                    <td>
                        <?php if (!empty($s['cpanel_url'])): ?>
                        <a href="<?= e($s['cpanel_url']) ?>" target="_blank" class="btn btn-outline btn-sm">cPanel Aç</a>
                        <?php else: ?>
                        <span style="color:var(--muted);font-size:.83rem">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= hostingBadge($s['status'] ?? 'active') ?></td>
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
            <thead><tr><th>Tür</th><th>Mesaj</th><th>Durum</th><th>Tarih</th></tr></thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= e(hReqTypeLabel($r['request_type'])) ?></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= e(mb_strimwidth($r['message'] ?? '—', 0, 60, '...')) ?></td>
                    <td><?= kanbanBadge2($r['kanban_status'] ?? 'pending') ?></td>
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
            <strong>Yeni Hosting Talebi</strong>
            <button onclick="document.getElementById('newRequestModal').style.display='none'" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem">✕</button>
        </div>
        <form method="POST" action="/panel/hosting/talep">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Talep Türü</label>
                <select name="request_type" required>
                    <option value="ftp_info">FTP Bilgilerini Göster</option>
                    <option value="cpanel_info">cPanel Bilgilerini Göster</option>
                    <option value="backup">Yedek Al</option>
                    <option value="upgrade">Paket Yükseltme</option>
                    <option value="other">Diğer</option>
                </select>
            </div>
            <?php if (!empty($services)): ?>
            <div class="form-group">
                <label>Hosting Paketi (opsiyonel)</label>
                <select name="hosting_service_id">
                    <option value="">Seçin...</option>
                    <?php foreach ($services as $s): ?>
                    <option value="<?= (int)$s['id'] ?>"><?= e($s['package_name']) ?></option>
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
function hostingBadge(string $s): string {
    $m = ['active'=>'green','suspended'=>'orange','expired'=>'red'];
    $l = ['active'=>'Aktif','suspended'=>'Askıya Alındı','expired'=>'Sona Erdi'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
function kanbanBadge2(string $s): string {
    $m = ['pending'=>'gray','reviewing'=>'orange','in_progress'=>'blue','completed'=>'green','rejected'=>'red'];
    $l = ['pending'=>'Bekliyor','reviewing'=>'İnceleniyor','in_progress'=>'İşlemde','completed'=>'Tamamlandı','rejected'=>'Reddedildi'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
function hReqTypeLabel(string $t): string {
    $m = ['ftp_info'=>'FTP Bilgisi','cpanel_info'=>'cPanel Bilgisi','backup'=>'Yedek','upgrade'=>'Yükseltme','other'=>'Diğer'];
    return $m[$t] ?? ucfirst($t);
}
?>
