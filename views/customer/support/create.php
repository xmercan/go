<?php $layout = 'layouts/panel'; ?>

<div style="margin-bottom:1.25rem">
    <a href="/panel/destek" style="font-size:.85rem;color:var(--muted);text-decoration:none">← Destek Talepleri</a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-title" style="margin-bottom:1.25rem">Yeni Destek Talebi</div>

    <form method="POST" action="/panel/destek">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Konu</label>
            <input type="text" name="subject" value="<?= e(old('subject')) ?>" required placeholder="Sorununuzu kısaca özetleyin" maxlength="255">
        </div>

        <?php if (!empty($projects)): ?>
        <div class="form-group">
            <label>İlgili Proje (opsiyonel)</label>
            <select name="project_id">
                <option value="">Seçin...</option>
                <?php foreach ($projects as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
                <label>Kategori</label>
                <select name="category">
                    <option value="genel">Genel</option>
                    <option value="teknik">Teknik Sorun</option>
                    <option value="fatura">Fatura & Ödeme</option>
                    <option value="domain">Domain</option>
                    <option value="hosting">Hosting</option>
                    <option value="yazilim">Yazılım</option>
                </select>
            </div>
            <div class="form-group">
                <label>Öncelik</label>
                <select name="priority">
                    <option value="low">Düşük</option>
                    <option value="normal" selected>Normal</option>
                    <option value="high">Yüksek</option>
                    <option value="urgent">Acil</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Mesaj</label>
            <textarea name="message" rows="6" required placeholder="Sorununuzu detaylı açıklayın..."><?= e(old('message')) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Destek Talebi Gönder</button>
    </form>
</div>
