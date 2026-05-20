<?php $layout = 'layouts/admin'; ?>

<style>
.settings-tabs { display:flex; gap:.4rem; margin-bottom:1.5rem; background:var(--card); border:1px solid var(--border); border-radius:10px; padding:.35rem; width:fit-content; flex-wrap:wrap; }
.settings-tab { padding:.4rem .85rem; border-radius:6px; font-size:.83rem; font-weight:600; text-decoration:none; color:var(--muted); cursor:pointer; transition:background .2s; background:none; border:none; }
.settings-tab.active, .settings-tab:hover { background:var(--blue); color:#0A1628; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
</style>

<div class="settings-tabs">
    <button class="settings-tab active" data-tab="site">🌐 Site</button>
    <button class="settings-tab" data-tab="smtp">📧 SMTP</button>
    <button class="settings-tab" data-tab="features">⚡ Özellikler</button>
</div>

<!-- Site Settings -->
<div class="tab-pane active" id="tab-site">
<div class="card" style="max-width:720px">
    <div class="card-title" style="margin-bottom:1.25rem">Site Ayarları</div>
    <form method="POST" action="/admin/ayarlar/site">
        <?= csrf_field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label>Site Adı</label>
                <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? 'GO.NET.TR') ?>">
            </div>
            <div class="form-group">
                <label>Site URL</label>
                <input type="url" name="site_url" value="<?= e($settings['site_url'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Site Açıklaması (SEO)</label>
            <textarea name="site_description" rows="2"><?= e($settings['site_description'] ?? '') ?></textarea>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>İletişim E-posta</label>
                <input type="email" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>İletişim Telefon</label>
                <input type="tel" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>">
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Firma Adı</label>
                <input type="text" name="company_name" value="<?= e($settings['company_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Fatura Prefix</label>
                <input type="text" name="invoice_prefix" value="<?= e($settings['invoice_prefix'] ?? 'GO-') ?>">
            </div>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:.75rem">
            <input type="checkbox" id="maintenance" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?> style="width:auto">
            <label for="maintenance" style="margin-bottom:0">Bakım modu (site erişimi kapalı)</label>
        </div>
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>
</div>
</div>

<!-- SMTP -->
<div class="tab-pane" id="tab-smtp">
<div class="card" style="max-width:720px;margin-bottom:1.25rem">
    <div class="card-title" style="margin-bottom:1.25rem">SMTP Ayarları</div>
    <form method="POST" action="/admin/ayarlar/smtp">
        <?= csrf_field() ?>
        <div class="grid-2">
            <div class="form-group">
                <label>SMTP Sunucu</label>
                <input type="text" name="smtp_host" value="<?= e($smtp['host'] ?? '') ?>" placeholder="mail.go.net.tr">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="number" name="smtp_port" value="<?= e($smtp['port'] ?? '587') ?>">
            </div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Şifreleme</label>
                <select name="smtp_encryption">
                    <?php foreach (['tls'=>'TLS','ssl'=>'SSL','none'=>'Yok'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= ($smtp['encryption'] ?? 'tls') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" name="smtp_user" value="<?= e($smtp['username'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Şifre <span style="color:var(--muted);font-size:.78rem">(boş bırakırsanız değişmez)</span></label>
            <input type="password" name="smtp_pass" placeholder="••••••••">
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Gönderici E-posta</label>
                <input type="email" name="smtp_from_email" value="<?= e($smtp['from_email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Gönderici Adı</label>
                <input type="text" name="smtp_from_name" value="<?= e($smtp['from_name'] ?? 'GO!') ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">SMTP Kaydet</button>
    </form>
</div>

<div class="card" style="max-width:720px">
    <div class="card-title" style="margin-bottom:1rem">📧 SMTP Test</div>
    <form method="POST" action="/admin/ayarlar/smtp-test">
        <?= csrf_field() ?>
        <div style="display:flex;gap:.75rem">
            <input type="email" name="test_email" placeholder="test@mail.com" style="flex:1" required>
            <button type="submit" class="btn btn-outline">Test Gönder</button>
        </div>
    </form>
</div>
</div>

<!-- Feature Flags -->
<div class="tab-pane" id="tab-features">
<div class="card" style="max-width:720px">
    <div class="card-title" style="margin-bottom:1.25rem">Özellik Bayrakları</div>
    <form method="POST" action="/admin/ayarlar/ozellikler">
        <?= csrf_field() ?>
        <?php $flags = [
            'ai_enabled'             => 'AI Entegrasyonu (GO! Chat AI)',
            'kanban_enabled'         => 'Kanban Yönetim Panosu',
            'zip_export_enabled'     => 'ZIP Dosya Export',
            'domain_module_enabled'  => 'Domain Modülü',
            'hosting_module_enabled' => 'Hosting Modülü',
            'software_module_enabled'=> 'Yazılım Modülü',
            'chat_enabled'           => 'GO! Chat',
            'api_enabled'            => 'API Erişimi',
            'notifications_enabled'  => 'Bildirimler',
            'support_enabled'        => 'Destek Sistemi',
        ]; ?>
        <div style="display:flex;flex-direction:column;gap:.85rem">
            <?php foreach ($flags as $key => $label): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem;background:var(--bg2);border:1px solid var(--border);border-radius:8px">
                <label for="flag_<?= $key ?>" style="cursor:pointer;margin:0;color:var(--text);font-size:.9rem"><?= e($label) ?></label>
                <label style="position:relative;display:inline-block;width:46px;height:26px;cursor:pointer;margin:0">
                    <input type="checkbox" id="flag_<?= $key ?>" name="<?= $key ?>" value="1" <?= ($settings[$key] ?? '0') === '1' ? 'checked' : '' ?> style="opacity:0;width:0;height:0">
                    <span style="position:absolute;cursor:pointer;inset:0;background:rgba(255,255,255,.1);border-radius:26px;transition:.3s" class="toggle-track"></span>
                </label>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.25rem">Bayrakları Kaydet</button>
    </form>
</div>
</div>

<script>
document.querySelectorAll('.settings-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.settings-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

// Toggle visual
document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
    const track = cb.nextElementSibling;
    if (!track) return;
    function update() {
        track.style.background = cb.checked ? '#00D4FF' : 'rgba(255,255,255,.1)';
        track.style.setProperty('--tw', cb.checked ? '20px' : '2px');
    }
    cb.addEventListener('change', update);
    update();
});
</script>
