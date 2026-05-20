<?php
/**
 * GO! Install Tanılama Sayfası
 * Sorun varsa bu dosyayı açın: go.net.tr/install/diag.php
 * Kurulum tamamlandıktan sonra SİLİN!
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$checks = [];

// PHP versiyonu
$phpVer = PHP_VERSION;
$phpOk  = version_compare($phpVer, '8.0.0', '>=');
$checks[] = ['PHP Versiyonu', $phpVer, $phpOk, $phpOk ? '' : 'PHP 8.0+ gerekli'];

// Gerekli uzantılar
foreach (['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'openssl', 'zip', 'session'] as $ext) {
    $ok = extension_loaded($ext);
    $checks[] = ['Uzantı: ' . $ext, $ok ? 'Yüklü' : 'EKSİK', $ok, $ok ? '' : "php.ini'de {$ext} aktif değil"];
}

// Yazılabilir klasörler
$dirs = [
    dirname(__DIR__) . '/storage'         => 'storage/',
    dirname(__DIR__) . '/storage/logs'    => 'storage/logs/',
    dirname(__DIR__) . '/storage/cache'   => 'storage/cache/',
    dirname(__DIR__) . '/storage/exports' => 'storage/exports/',
    dirname(__DIR__) . '/storage/sandbox' => 'storage/sandbox/',
    dirname(__DIR__) . '/uploads'         => 'uploads/',
    dirname(__DIR__)                       => 'root (config.php için)',
];
foreach ($dirs as $path => $label) {
    $exists   = is_dir($path);
    $writable = $exists && is_writable($path);
    $checks[] = [
        $label,
        $exists ? ($writable ? 'Yazılabilir' : 'Yazılamıyor') : 'YOK',
        $writable,
        !$exists ? 'Klasör yok' : (!$writable ? 'chmod 755 uygulayın' : ''),
    ];
}

// Kritik dosyalar
$files = [
    dirname(__DIR__) . '/database/GoV1.sql'   => 'database/GoV1.sql',
    dirname(__DIR__) . '/install/index.php'    => 'install/index.php',
    dirname(__DIR__) . '/install/steps/import.php' => 'install/steps/import.php',
    dirname(__DIR__) . '/install/steps/admin.php'  => 'install/steps/admin.php',
];
foreach ($files as $path => $label) {
    $exists = file_exists($path);
    $size   = $exists ? filesize($path) : 0;
    $checks[] = [$label, $exists ? number_format($size) . ' bytes' : 'EKSİK', $exists, $exists ? '' : 'Dosya yok'];
}

// .env durumu
$envPath = dirname(__DIR__) . '/.env';
$envExists = file_exists($envPath);
$checks[] = ['.env dosyası', $envExists ? 'Var' : 'Yok (install sonrası oluşur)', true, ''];

// lock durumu
$lockPath = dirname(__DIR__) . '/storage/installed.lock';
$lockExists = file_exists($lockPath);
$checks[] = ['installed.lock', $lockExists ? 'VAR (kurulum kilitli)' : 'Yok (kurulum açık)', true, ''];

// Hepsi ok mi?
$allOk = array_reduce($checks, fn($c, $i) => $c && $i[2], true);

// mod_rewrite
$rewriteOk = isset($_SERVER['HTTP_X_REWRITE_TEST']) || function_exists('apache_get_modules')
    ? in_array('mod_rewrite', apache_get_modules())
    : null;

?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GO! Tanılama</title>
<style>
body{background:#0A1628;color:#fff;font-family:system-ui;padding:2rem;max-width:800px;margin:0 auto}
h1{color:#00D4FF;margin-bottom:.5rem}
.sub{color:rgba(255,255,255,.5);margin-bottom:2rem}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:.6rem .8rem;background:rgba(255,255,255,.05);font-size:.8rem;color:rgba(255,255,255,.5)}
td{padding:.6rem .8rem;border-bottom:1px solid rgba(255,255,255,.07);font-size:.85rem}
.ok{color:#22C55E}
.fail{color:#EF4444;font-weight:700}
.warn{color:#F97316}
.note{color:rgba(255,255,255,.4);font-size:.78rem}
.summary{padding:1rem;border-radius:8px;margin-bottom:1.5rem;font-weight:600}
.summary.ok{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#86efac}
.summary.fail{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
a{color:#00D4FF}
</style>
</head>
<body>
<h1>GO! Tanılama Sayfası</h1>
<p class="sub">Sorun giderme için kullanın. Kurulum tamamlandıktan sonra bu dosyayı <strong>silin</strong>.</p>

<div class="summary <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk ? '✅ Tüm kontroller geçti — kurulum yapılabilir.' : '❌ Bazı kontroller başarısız. Aşağıdaki hataları düzeltin.' ?>
</div>

<table>
<thead><tr><th>Kontrol</th><th>Durum</th><th>Not</th></tr></thead>
<tbody>
<?php foreach ($checks as $c): ?>
<tr>
    <td><?= htmlspecialchars($c[0], ENT_QUOTES) ?></td>
    <td class="<?= $c[2] ? 'ok' : 'fail' ?>"><?= htmlspecialchars($c[1], ENT_QUOTES) ?></td>
    <td class="note"><?= htmlspecialchars($c[3] ?? '', ENT_QUOTES) ?></td>
</tr>
<?php endforeach; ?>
<?php if ($rewriteOk !== null): ?>
<tr>
    <td>mod_rewrite</td>
    <td class="<?= $rewriteOk ? 'ok' : 'fail' ?>"><?= $rewriteOk ? 'Aktif' : 'İnaktif' ?></td>
    <td class="note"><?= $rewriteOk ? '' : 'Apache AllowOverride All gerekli' ?></td>
</tr>
<?php endif; ?>
</tbody>
</table>

<br>
<p class="note">PHP: <?= PHP_VERSION ?> | Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor' ?> | OS: <?= PHP_OS ?></p>
<p class="note">SAPI: <?= PHP_SAPI ?> | Memory: <?= ini_get('memory_limit') ?> | Max execution: <?= ini_get('max_execution_time') ?>s</p>

<br>
<?php if (!$lockExists): ?>
<a href="/install/">→ Kuruluma Git</a>
<?php else: ?>
<p class="note">⚠️ installed.lock var, kurulum kilitli.</p>
<?php endif; ?>
</body>
</html>
