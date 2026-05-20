<?php

/**
 * Adım 6: Kurulum Tamamlandı
 * installed.lock oluşturur ve install klasörünü devre dışı bırakır.
 */

// installed.lock yaz
$lockFile = GO_ROOT . '/storage/installed.lock';
if (!file_exists($lockFile)) {
    file_put_contents($lockFile, date('Y-m-d H:i:s') . ' — GO! V1 kurulumu tamamlandı.');
}

// Install klasörünü kilitle: index.php'yi boş bir dosyayla değiştirme yerine
// .htaccess ile engelle
$installHtaccess = INSTALL_ROOT . '/.htaccess';
$htContent = "# GO! kurulumu tamamlandı — bu klasöre erişim kapalı\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n";
file_put_contents($installHtaccess, $htContent);

// Session temizle
$adminEmail = $_SESSION['admin_email']   ?? '';
$siteUrl    = $_SESSION['site_url']      ?? '';
session_destroy();

install_head('Kurulum Tamamlandı!', 6, $stepTitles);
?>

<div style="text-align:center;padding:1rem 0">
    <div style="font-size:4rem;margin-bottom:1rem">🎉</div>
    <h3 style="color:#22C55E;font-size:1.4rem;margin-bottom:1rem">GO! V1 başarıyla kuruldu!</h3>
    <p style="color:rgba(255,255,255,.6);margin-bottom:2rem">
        Kurulum tamamlandı ve güvenli şekilde kilitleniyor.<br>
        Admin hesabınıza giriş yapabilirsiniz.
    </p>
</div>

<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px;padding:1rem;margin-bottom:1.5rem">
    <p style="font-size:.85rem;color:rgba(255,255,255,.6);margin-bottom:.5rem">Admin E-posta</p>
    <p style="font-weight:600"><?= htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') ?></p>
    <p style="font-size:.8rem;color:rgba(255,255,255,.4);margin-top:.5rem">Şifrenizi kurulum sırasında belirlediğiniz şifreyle giriş yapın.</p>
</div>

<div style="background:rgba(0,212,255,.05);border:1px solid rgba(0,212,255,.15);border-radius:8px;padding:1rem;margin-bottom:1.5rem;font-size:.85rem;color:rgba(255,255,255,.6)">
    <strong style="color:#00D4FF">Güvenlik notu:</strong> /install/ klasörü otomatik kilitlendi.
    Güvenliğiniz için bu klasörü sunucudan silmenizi veya FTP ile tamamen kaldırmanızı öneriyoruz.
</div>

<a href="<?= htmlspecialchars(($siteUrl ?: '') . '/admin/giris', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-full">
    Admin Paneline Git →
</a>

<?php
install_foot();
