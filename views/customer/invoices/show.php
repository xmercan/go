<?php $layout = 'layouts/panel'; ?>

<style>
.invoice-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    flex-wrap: wrap; gap: 1.5rem; margin-bottom: 2rem;
}
.invoice-logo { font-size: 2rem; font-weight: 900; color: var(--blue); }
.invoice-from { font-size: .85rem; color: var(--muted); margin-top: .5rem; }
.invoice-meta { text-align: right; }
.invoice-meta h1 { font-size: 1.5rem; font-weight: 900; margin-bottom: .25rem; }
.invoice-meta .meta-row { font-size: .83rem; color: var(--muted); margin-bottom: .2rem; }

.invoice-table th { background: var(--bg3); }
.invoice-total-row td { font-size: 1rem; font-weight: 700; }

.payment-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
}
.payment-iban {
    font-family: monospace;
    font-size: 1rem;
    letter-spacing: .05em;
    color: var(--blue);
    background: var(--blue-dim);
    padding: .5rem 1rem;
    border-radius: 8px;
    margin: .75rem 0;
    word-break: break-all;
}
</style>

<div class="card" style="margin-bottom:1.5rem">
    <div class="invoice-header">
        <div>
            <div class="invoice-logo">GO!</div>
            <div class="invoice-from">Genç Grup Yazılım Ltd. Şti.<br>yatirim@go.net.tr</div>
        </div>
        <div class="invoice-meta">
            <h1>Fatura</h1>
            <div class="meta-row">No: <strong>#<?= e($invoice['invoice_no']) ?></strong></div>
            <div class="meta-row">Tarih: <?= date('d.m.Y', strtotime($invoice['created_at'])) ?></div>
            <?php if ($invoice['due_date']): ?>
            <div class="meta-row">Vade: <?= date('d.m.Y', strtotime($invoice['due_date'])) ?></div>
            <?php endif; ?>
            <div style="margin-top:.5rem"><?= invStatusBadge($invoice['status']) ?></div>
        </div>
    </div>

    <p style="font-size:.85rem;color:var(--muted);margin-bottom:1.25rem">
        Fatura Sahibi: <strong><?= e($invoice['user_name'] ?? '') ?></strong>
        — <?= e($invoice['user_email'] ?? '') ?>
    </p>

    <!-- Items table -->
    <div class="table-wrap" style="margin-bottom:1.5rem">
        <table>
            <thead><tr><th>#</th><th>Açıklama</th><th>Miktar</th><th>Birim</th><th>Toplam</th></tr></thead>
            <tbody>
                <?php foreach ($items ?? [] as $i => $item): ?>
                <tr>
                    <td style="color:var(--muted)"><?= $i + 1 ?></td>
                    <td><?= e($item['description']) ?></td>
                    <td><?= number_format((float)$item['quantity'], 0) ?></td>
                    <td><?= number_format((float)$item['unit_price'], 2, ',', '.') ?>₺</td>
                    <td><strong><?= number_format((float)$item['total'], 2, ',', '.') ?>₺</strong></td>
                </tr>
                <?php endforeach; ?>
                <tr style="border-top:2px solid var(--border)">
                    <td colspan="4" style="text-align:right;color:var(--muted);font-size:.85rem">Ara Toplam</td>
                    <td><?= number_format((float)$invoice['subtotal'], 2, ',', '.') ?>₺</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;color:var(--muted);font-size:.85rem">KDV (%<?= (int)$invoice['tax_rate'] ?>)</td>
                    <td><?= number_format((float)$invoice['tax_amount'], 2, ',', '.') ?>₺</td>
                </tr>
                <tr class="invoice-total-row" style="background:var(--blue-dim)">
                    <td colspan="4" style="text-align:right;padding-right:1rem">Genel Toplam</td>
                    <td style="color:var(--blue)"><?= number_format((float)$invoice['total'], 2, ',', '.') ?>₺</td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if (!empty($invoice['notes'])): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:8px;padding:.85rem 1rem;font-size:.88rem;color:var(--muted)">
        <strong style="color:var(--text)">Not:</strong> <?= e($invoice['notes']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Payment section -->
<?php if (in_array($invoice['status'], ['waiting','payment_pending'], true)): ?>
<div style="margin-bottom:1.25rem">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem">💳 Ödeme Yöntemleri</h3>
    <?php if (empty($paymentMethods)): ?>
    <div class="alert alert-info">Ödeme bilgileri hazırlanıyor. Yakında burada görünecek.</div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:1rem">
        <?php foreach ($paymentMethods as $pm): ?>
        <?php if ($pm['type'] === 'bank_transfer'): ?>
        <div class="payment-card">
            <p style="font-weight:700;margin-bottom:.75rem">🏦 Banka Transferi</p>
            <?php if (!empty($pm['bank_name'])): ?>
            <p style="font-size:.85rem;color:var(--muted)">Banka: <strong><?= e($pm['bank_name']) ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($pm['account_holder'])): ?>
            <p style="font-size:.85rem;color:var(--muted)">Hesap Sahibi: <strong><?= e($pm['account_holder']) ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($pm['iban'])): ?>
            <div class="payment-iban"><?= e($pm['iban']) ?></div>
            <button onclick="navigator.clipboard.writeText('<?= e($pm['iban']) ?>').then(()=>alert('IBAN kopyalandı!'))" class="btn btn-outline btn-sm">IBAN Kopyala</button>
            <?php endif; ?>
            <?php if (!empty($pm['instructions'])): ?>
            <p style="font-size:.83rem;color:var(--muted);margin-top:.75rem"><?= nl2br(e($pm['instructions'])) ?></p>
            <?php endif; ?>
        </div>
        <?php elseif ($pm['type'] === 'online_link'): ?>
        <div class="payment-card">
            <p style="font-weight:700;margin-bottom:.75rem">🔗 Online Ödeme</p>
            <?php if (!empty($pm['online_url'])): ?>
            <a href="<?= e($pm['online_url']) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                Güvenli Ödeme Sayfasına Git →
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Notify payment -->
<?php $alreadyReported = !empty(array_filter($notifications ?? [], fn($n) => $n['status'] === 'pending')); ?>
<?php if (!$alreadyReported): ?>
<div class="card">
    <div class="card-title" style="margin-bottom:1rem">📩 Ödeme Bildirimi Gönder</div>
    <p style="font-size:.88rem;color:var(--muted);margin-bottom:1.25rem">
        Ödemenizi yaptıktan sonra bize bildirin. Ekibimiz en kısa sürede onaylayacak.
    </p>
    <form method="POST" action="/panel/faturalar/<?= e($invoice['uuid']) ?>/bildir">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Ödeme Yöntemi</label>
            <select name="method_type">
                <option value="bank_transfer">Banka Transferi</option>
                <option value="online_link">Online Link ile Ödeme</option>
            </select>
        </div>
        <div class="form-group">
            <label>Not (opsiyonel)</label>
            <textarea name="user_note" rows="2" placeholder="Ödeme referans numarası, açıklama vb."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Ödeme Bildirimi Gönder</button>
    </form>
</div>
<?php else: ?>
<div style="background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);border-radius:10px;padding:1rem 1.25rem">
    ✅ Ödeme bildiriminiz alındı. Ekibimiz inceleniyor.
</div>
<?php endif; ?>
<?php endif; ?>

<?php
function invStatusBadge(string $s): string {
    $m = ['draft'=>'gray','waiting'=>'orange','payment_pending'=>'blue','payment_reported'=>'blue','paid'=>'green','cancelled'=>'red'];
    $l = ['draft'=>'Taslak','waiting'=>'Ödeme Bekliyor','payment_pending'=>'Bekliyor','payment_reported'=>'Bildirim Alındı','paid'=>'Ödendi','cancelled'=>'İptal'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
?>
