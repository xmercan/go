<?php $layout = 'layouts/auth'; ?>

<form method="POST" action="/giris" novalidate>
    <?= csrf_field() ?>
    <?php if (!empty($redirect)): ?>
    <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="email">E-posta Adresi</label>
        <input type="email" id="email" name="email"
               value="<?= e(old('email')) ?>"
               placeholder="ornek@mail.com" required autofocus>
    </div>

    <div class="form-group">
        <label for="password">Şifre</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
    </div>

    <div style="text-align:right;margin-bottom:1.25rem">
        <a href="/sifremi-unuttum" style="font-size:.83rem;color:var(--blue);text-decoration:none">Şifremi Unuttum</a>
    </div>

    <button type="submit" class="btn btn-primary">Giriş Yap</button>
</form>

<div class="divider">veya</div>

<div class="auth-links">
    Henüz hesabınız yok mu? <a href="/kayit">Ücretsiz Kayıt Ol</a>
</div>
