<?php

/**
 * Adım 1: Sistem Gereksinimleri Kontrolü
 */

$checks = [];
$allOk  = true;

// PHP sürümü
$phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
$checks[] = ['label' => 'PHP >= 8.1', 'status' => $phpOk ? 'ok' : 'fail', 'value' => PHP_VERSION, 'required' => true];
if (!$phpOk) $allOk = false;

// PDO
$pdoOk = extension_loaded('pdo') && extension_loaded('pdo_mysql');
$checks[] = ['label' => 'PDO + PDO_MySQL', 'status' => $pdoOk ? 'ok' : 'fail', 'value' => $pdoOk ? 'Aktif' : 'Yok', 'required' => true];
if (!$pdoOk) $allOk = false;

// mbstring
$mbOk = extension_loaded('mbstring');
$checks[] = ['label' => 'mbstring', 'status' => $mbOk ? 'ok' : 'fail', 'value' => $mbOk ? 'Aktif' : 'Yok', 'required' => true];
if (!$mbOk) $allOk = false;

// json
$jsonOk = extension_loaded('json');
$checks[] = ['label' => 'JSON', 'status' => $jsonOk ? 'ok' : 'fail', 'value' => $jsonOk ? 'Aktif' : 'Yok', 'required' => true];
if (!$jsonOk) $allOk = false;

// curl
$curlOk = extension_loaded('curl');
$checks[] = ['label' => 'cURL', 'status' => $curlOk ? 'ok' : 'warn', 'value' => $curlOk ? 'Aktif' : 'Yok — SMTP için önerilir', 'required' => false];

// openssl
$sslOk = extension_loaded('openssl');
$checks[] = ['label' => 'OpenSSL', 'status' => $sslOk ? 'ok' : 'warn', 'value' => $sslOk ? 'Aktif' : 'Yok — Şifreleme için önerilir', 'required' => false];

// zip
$zipOk = extension_loaded('zip');
$checks[] = ['label' => 'ZipArchive', 'status' => $zipOk ? 'ok' : 'warn', 'value' => $zipOk ? 'Aktif' : 'Yok — Proje export için önerilir', 'required' => false];

// uploads/ yazılabilir
$uploadsPath = GO_ROOT . '/uploads';
$uploadsOk   = is_writable($uploadsPath) || @mkdir($uploadsPath, 0755, true);
$checks[] = ['label' => 'uploads/ yazılabilir', 'status' => $uploadsOk ? 'ok' : 'fail', 'value' => $uploadsOk ? 'Evet' : 'Hayır — chmod 755 yapın', 'required' => true];
if (!$uploadsOk) $allOk = false;

// storage/ yazılabilir
$storagePath = GO_ROOT . '/storage';
$storageDirs = ['cache', 'logs', 'sandbox', 'exports', 'app'];
$storageOk   = true;
foreach ($storageDirs as $dir) {
    $path = $storagePath . '/' . $dir;
    if (!is_dir($path)) @mkdir($path, 0755, true);
    if (!is_writable($path)) { $storageOk = false; break; }
}
$checks[] = ['label' => 'storage/ yazılabilir', 'status' => $storageOk ? 'ok' : 'fail', 'value' => $storageOk ? 'Evet' : 'Hayır — chmod 755 yapın', 'required' => true];
if (!$storageOk) $allOk = false;

// config.php yazılabilir
$configOk = is_writable(GO_ROOT) || is_writable(GO_ROOT . '/config.php');
$checks[] = ['label' => 'config.php yazılabilir', 'status' => $configOk ? 'ok' : 'fail', 'value' => $configOk ? 'Evet' : 'Hayır — root klasör yazılabilir olmalı', 'required' => true];
if (!$configOk) $allOk = false;

// .env yazılabilir
$envWritable = is_writable(GO_ROOT) || (file_exists(GO_ROOT . '/.env') && is_writable(GO_ROOT . '/.env'));
$checks[] = ['label' => '.env dosyası yazılabilir', 'status' => $envWritable ? 'ok' : 'warn', 'value' => $envWritable ? 'Evet' : 'Dikkat — manuel oluşturmanız gerekebilir', 'required' => false];

// POST isteği ile ilerle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $allOk) {
    $_SESSION['install_completed'][] = 1;
    header('Location: /install/?step=2');
    exit;
}

// Render
install_head('Sistem Gereksinimleri', $step, $stepTitles);
?>

<?php if (!$allOk): ?>
<div class="alert alert-error">Bazı zorunlu gereksinimler karşılanmıyor. Lütfen hosting ayarlarınızı düzenleyin ve sayfayı yenileyin.</div>
<?php else: ?>
<div class="alert alert-success">Tüm zorunlu gereksinimler karşılanıyor. Devam edebilirsiniz.</div>
<?php endif; ?>

<ul class="check-list">
<?php foreach ($checks as $c): ?>
    <li>
        <span class="badge-<?= $c['status'] ?>">
            <?= $c['status'] === 'ok' ? '✓' : ($c['status'] === 'fail' ? '✗' : '⚠') ?>
        </span>
        <span><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></span>
        <span style="margin-left:auto;font-size:.8rem;color:rgba(255,255,255,.5)">
            <?= htmlspecialchars($c['value'], ENT_QUOTES, 'UTF-8') ?>
        </span>
    </li>
<?php endforeach; ?>
</ul>

<form method="POST" style="margin-top:1.5rem">
    <button type="submit" class="btn btn-primary btn-full" <?= !$allOk ? 'disabled' : '' ?>>
        Devam Et →
    </button>
</form>

<?php
install_foot();
