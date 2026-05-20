<?php $layout = 'layouts/auth'; ?>

<p style="font-size:.85rem;color:var(--muted);text-align:center;margin-bottom:1.5rem">
    Yalnızca yetkili personel bu panele erişebilir.
</p>

<form method="POST" action="/admin/giris" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">Admin E-posta</label>
        <input type="email" id="email" name="email"
               placeholder="admin@go.net.tr" required autofocus>
    </div>

    <div class="form-group">
        <label for="password">Şifre</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn btn-primary">Admin Girişi</button>
</form>

<div class="auth-links" style="margin-top:1.25rem;font-size:.8rem">
    <a href="/" style="color:var(--muted);text-decoration:none">← Ana Sayfa</a>
</div>
