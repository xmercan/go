<?php $layout = 'layouts/auth'; ?>

<p style="font-size:.9rem;color:var(--muted);text-align:center;margin-bottom:1.5rem">
    Kayıtlı e-posta adresinizi girin, şifre sıfırlama bağlantısı gönderelim.
</p>

<form method="POST" action="/sifremi-unuttum" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">E-posta Adresi</label>
        <input type="email" id="email" name="email"
               value="<?= e(old('email')) ?>"
               placeholder="ornek@mail.com" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary">Sıfırlama Bağlantısı Gönder</button>
</form>

<div class="auth-links" style="margin-top:1.1rem">
    <a href="/giris">← Giriş Sayfasına Dön</a>
</div>
