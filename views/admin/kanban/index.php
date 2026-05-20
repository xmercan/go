<?php $layout = 'layouts/admin'; ?>

<style>
.kanban-tabs {
    display: flex; gap: .4rem; margin-bottom: 1.5rem; flex-wrap: wrap;
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: .4rem; width: fit-content;
}
.kanban-tab {
    padding: .45rem 1rem; border-radius: 8px; font-size: .83rem; font-weight: 600;
    text-decoration: none; color: var(--muted); transition: background .2s, color .2s;
}
.kanban-tab:hover { color: var(--text); background: var(--card-h); }
.kanban-tab.active { background: var(--blue); color: #0A1628; }

.kanban-board {
    display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem;
    min-height: calc(100vh - 200px);
}
.kanban-col {
    min-width: 260px; max-width: 280px; flex-shrink: 0;
    display: flex; flex-direction: column; gap: .75rem;
}
.kanban-col-header {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: .6rem 1rem; font-size: .8rem; font-weight: 700;
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
}
.kanban-col-count {
    background: var(--bg3); border-radius: 10px; padding: .1rem .45rem;
    font-size: .72rem; font-weight: 600; color: var(--muted);
}
.kanban-cards { display: flex; flex-direction: column; gap: .6rem; min-height: 100px; }
.kanban-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    padding: .85rem 1rem; cursor: grab; transition: box-shadow .2s, border-color .2s;
    user-select: none;
}
.kanban-card:active { cursor: grabbing; }
.kanban-card.dragging { opacity: .5; box-shadow: 0 8px 24px rgba(0,0,0,.4); }
.kanban-col.drag-over .kanban-cards { border: 2px dashed var(--blue); border-radius: 10px; padding: .5rem; }
.card-user { font-size: .75rem; color: var(--muted); margin-bottom: .4rem; }
.card-title-text { font-size: .88rem; font-weight: 600; margin-bottom: .35rem; word-break: break-word; }
.card-meta { font-size: .75rem; color: var(--muted); display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
.card-priority { font-weight: 700; }
.priority-urgent { color: var(--red); }
.priority-high   { color: var(--orange); }
.priority-normal { color: var(--muted); }
.priority-low    { color: rgba(255,255,255,.2); }

.col-pending     { border-top: 3px solid rgba(255,255,255,.2); }
.col-reviewing   { border-top: 3px solid var(--orange); }
.col-in_progress { border-top: 3px solid var(--blue); }
.col-completed   { border-top: 3px solid var(--green); }
.col-rejected    { border-top: 3px solid var(--red); }
</style>

<!-- Type tabs -->
<div class="kanban-tabs">
    <?php $types = ['domain'=>'🌐 Domain','hosting'=>'🖥️ Hosting','software'=>'💻 Yazılım','support'=>'🎯 Destek','payment'=>'💳 Ödeme']; ?>
    <?php foreach ($types as $t => $label): ?>
    <a href="/admin/kanban?type=<?= $t ?>" class="kanban-tab <?= $t === ($type ?? 'domain') ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>

<!-- Board -->
<div class="kanban-board" id="kanbanBoard">
    <?php
    $colLabels = [
        'pending'     => '⏳ Bekliyor',
        'reviewing'   => '🔍 İnceleniyor',
        'in_progress' => '⚡ İşlemde',
        'completed'   => '✅ Tamamlandı',
        'rejected'    => '❌ Reddedildi',
    ];
    foreach ($columns as $col):
        $colCards = array_values($board[$col] ?? []);
    ?>
    <div class="kanban-col col-<?= $col ?>" data-col="<?= $col ?>">
        <div class="kanban-col-header">
            <span><?= $colLabels[$col] ?></span>
            <span class="kanban-col-count"><?= count($colCards) ?></span>
        </div>
        <div class="kanban-cards" id="col-<?= $col ?>">
            <?php foreach ($colCards as $card): ?>
            <div class="kanban-card"
                 draggable="true"
                 data-id="<?= (int)$card['id'] ?>"
                 data-type="<?= e($type) ?>"
                 data-status="<?= e($col) ?>">
                <div class="card-user"><?= e($card['user_name'] ?? '—') ?></div>
                <div class="card-title-text">
                    <?php if ($type === 'support'): ?>
                        <?= e(mb_strimwidth($card['subject'] ?? '', 0, 50, '...')) ?>
                    <?php elseif ($type === 'payment'): ?>
                        💳 #<?= e($card['invoice_no'] ?? '') ?> — <?= number_format((float)($card['total'] ?? 0), 0, ',', '.') ?>₺
                    <?php else: ?>
                        <?= e(mb_strimwidth($card['domain_name'] ?? $card['package_name'] ?? $card['product_name'] ?? 'Talep #' . $card['id'], 0, 50, '...')) ?>
                    <?php endif; ?>
                </div>
                <div class="card-meta">
                    <span class="card-priority priority-<?= e($card['priority'] ?? 'normal') ?>">
                        <?= priorityIcon($card['priority'] ?? 'normal') ?>
                    </span>
                    <span><?= date('d.m', strtotime($card['created_at'])) ?></span>
                    <?php if (isset($card['request_type'])): ?>
                    <span style="background:var(--bg3);padding:.1rem .4rem;border-radius:4px"><?= e($card['request_type']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
(function() {
    let dragCard = null;
    const csrf   = '<?= csrf_token() ?>';
    const type   = '<?= e($type ?? '') ?>';

    document.querySelectorAll('.kanban-card').forEach(card => {
        card.addEventListener('dragstart', () => {
            dragCard = card;
            setTimeout(() => card.classList.add('dragging'), 0);
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            dragCard = null;
        });
    });

    document.querySelectorAll('.kanban-col').forEach(col => {
        col.addEventListener('dragover', e => {
            e.preventDefault();
            col.classList.add('drag-over');
        });
        col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
        col.addEventListener('drop', async e => {
            e.preventDefault();
            col.classList.remove('drag-over');

            if (!dragCard) return;

            const newStatus = col.dataset.col;
            const id        = dragCard.dataset.id;
            const oldStatus = dragCard.dataset.status;

            if (oldStatus === newStatus) return;

            // Move card in DOM
            const cardContainer = col.querySelector('.kanban-cards');
            cardContainer.appendChild(dragCard);
            dragCard.dataset.status = newStatus;

            // Update count badges
            updateCounts();

            // API call
            const fd = new FormData();
            fd.append('id', id);
            fd.append('type', type);
            fd.append('status', newStatus);
            fd.append('sort_order', cardContainer.children.length);
            fd.append('csrf_token', csrf);

            try {
                await fetch('/admin/kanban/guncelle', {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: fd
                });
            } catch(err) {
                console.error('Kanban update failed', err);
            }
        });
    });

    function updateCounts() {
        document.querySelectorAll('.kanban-col').forEach(col => {
            const count = col.querySelector('.kanban-cards').children.length;
            col.querySelector('.kanban-col-count').textContent = count;
        });
    }
})();
</script>

<?php
function priorityIcon(string $p): string {
    return match($p) { 'urgent'=>'🔴','high'=>'🟠','normal'=>'🟡','low'=>'⚪', default=>'⚪' };
}
?>
