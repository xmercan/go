<?php $layout = 'layouts/auth'; ?>

<form method="POST" action="/kayit" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="full_name">Ad Soyad</label>
        <input type="text" id="full_name" name="full_name"
               value="<?= e(old('full_name')) ?>"
               placeholder="Ahmet Yılmaz" required autofocus maxlength="150">
    </div>

    <div class="form-group">
        <label for="email">E-posta</label>
        <input type="email" id="email" name="email"
               value="<?= e(old('email')) ?>"
               placeholder="ornek@mail.com" required maxlength="191">
    </div>

    <div class="form-group">
        <label for="phone">Telefon</label>
        <input type="tel" id="phone" name="phone"
               value="<?= e(old('phone')) ?>"
               placeholder="05xx xxx xxxx" required>
    </div>

    <div class="form-group">
        <label for="password">Şifre <span style="color:var(--muted);font-size:.78rem">(en az 8 karakter)</span></label>
        <input type="password" id="password" name="password" required minlength="8" placeholder="••••••••" autocomplete="new-password">
    </div>

    <div class="form-group">
        <label for="password_confirmation">Şifre Tekrar</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
    </div>

    <p style="font-size:.78rem;color:var(--muted);margin-bottom:1.1rem">
        Kayıt olarak <a href="/kvkk" style="color:var(--blue)" target="_blank">KVKK Aydınlatma Metni</a>'ni ve
        <a href="/kullanim-kosullari" style="color:var(--blue)" target="_blank">Kullanım Koşulları</a>'nı kabul etmiş olursunuz.
    </p>

    <button type="submit" class="btn btn-primary">Ücretsiz Hesap Oluştur</button>
</form>

<div class="auth-links" style="margin-top:1.1rem">
    Zaten hesabınız var mı? <a href="/giris">Giriş Yap</a>
</div>
