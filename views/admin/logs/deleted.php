<?php $layout = 'layouts/admin'; ?>

<div style="margin-bottom:1.25rem">
    <h2 style="font-size:1.1rem;font-weight:800">🗑️ Silinen Kayıtlar</h2>
    <p style="font-size:.85rem;color:var(--muted)">Soft-delete ile silinen tüm kayıtların anlık görüntüsü</p>
</div>

<div class="card">
    <?php if (empty($records)): ?>
    <p style="color:var(--muted);text-align:center;padding:2rem 0;font-size:.9rem">Silinen kayıt bulunamadı.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tablo</th><th>Kayıt ID</th><th>Silen</th><th>Sebep</th><th>Tarih</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td><span class="badge badge-gray"><?= e($r['table_name']) ?></span></td>
                    <td style="font-size:.83rem">#<?= (int)$r['record_id'] ?></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= e($r['deleted_by_type']) ?> #<?= (int)($r['deleted_by_id'] ?? 0) ?></td>
                    <td style="font-size:.8rem;color:var(--muted)"><?= e(mb_strimwidth($r['reason'] ?? '—', 0, 50, '...')) ?></td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= date('d.m.Y H:i', strtotime($r['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-outline btn-sm"
                                onclick="showSnapshot(<?= htmlspecialchars(json_encode($r['record_snapshot'] ?? '{}'), ENT_QUOTES) ?>)">
                            Snapshot
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Snapshot Modal -->
<div id="snapshotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:500;align-items:center;justify-content:center;padding:1rem">
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:1.5rem;width:100%;max-width:600px;max-height:80vh;overflow-y:auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
            <strong>Kayıt Snapshot</strong>
            <button onclick="document.getElementById('snapshotModal').style.display='none'" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem">✕</button>
        </div>
        <pre id="snapshotContent" style="background:var(--bg3);padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;white-space:pre-wrap;color:var(--text)"></pre>
    </div>
</div>

<script>
function showSnapshot(data) {
    const modal = document.getElementById('snapshotModal');
    const pre   = document.getElementById('snapshotContent');
    try {
        const obj = typeof data === 'string' ? JSON.parse(data) : data;
        pre.textContent = JSON.stringify(obj, null, 2);
    } catch(e) {
        pre.textContent = String(data);
    }
    modal.style.display = 'flex';
}
</script>
