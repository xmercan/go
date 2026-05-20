<?php $layout = 'layouts/panel'; ?>

<div style="margin-bottom:1.5rem">
    <h2 style="font-size:1.1rem;font-weight:800">🧾 Faturalarım</h2>
    <p style="font-size:.85rem;color:var(--muted);margin-top:.2rem"><?= count($invoices ?? []) ?> fatura</p>
</div>

<?php if (empty($invoices)): ?>
<div style="text-align:center;padding:4rem 0;color:var(--muted)">
    <div style="font-size:3rem;margin-bottom:1rem">🧾</div>
    <p>Henüz faturanız bulunmuyor.</p>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Fatura No</th>
                    <th>Proje</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                    <th>Vade</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><strong><?= e($inv['invoice_no']) ?></strong></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= e($inv['project_name'] ?? '—') ?></td>
                    <td>
                        <strong><?= number_format((float)$inv['total'], 2, ',', '.') ?>₺</strong>
                        <span style="font-size:.75rem;color:var(--muted)">+KDV dahil</span>
                    </td>
                    <td><?= invoiceStatusBadge($inv['status'] ?? 'draft') ?></td>
                    <td style="font-size:.83rem;color:var(--muted)"><?= $inv['due_date'] ? date('d.m.Y', strtotime($inv['due_date'])) : '—' ?></td>
                    <td>
                        <a href="/panel/faturalar/<?= e($inv['uuid']) ?>" class="btn btn-outline btn-sm">Görüntüle</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
function invoiceStatusBadge(string $s): string {
    $m = ['draft'=>'gray','waiting'=>'orange','payment_pending'=>'blue','payment_reported'=>'blue','paid'=>'green','cancelled'=>'red'];
    $l = ['draft'=>'Taslak','waiting'=>'Ödeme Bekliyor','payment_pending'=>'Ödeme Bekleniyor','payment_reported'=>'Bildirim Alındı','paid'=>'Ödendi','cancelled'=>'İptal'];
    return '<span class="badge badge-'.($m[$s]??'gray').'">'.($l[$s]??ucfirst($s)).'</span>';
}
?>
