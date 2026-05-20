<?php $layout = 'layouts/auth'; ?>

<p style="font-size:.9rem;color:var(--muted);text-align:center;margin-bottom:1.5rem">
    Yeni şifrenizi belirleyin. Güçlü bir şifre seçmenizi öneririz.
</p>

<form method="POST" action="/sifre-sifirla/<?= e($token ?? '') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="password">Yeni Şifre <span style="color:var(--muted);font-size:.78rem">(en az 8 karakter)</span></label>
        <input type="password" id="password" name="password" required minlength="8" placeholder="••••••••" autofocus autocomplete="new-password">
    </div>

    <div class="form-group">
        <label for="password_confirmation">Şifre Tekrar</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn btn-primary">Şifremi Güncelle</button>
</form>

<div class="auth-links" style="margin-top:1.1rem">
    <a href="/giris">← Giriş Sayfasına Dön</a>
</div>
