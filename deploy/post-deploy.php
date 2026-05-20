#!/usr/bin/env php
<?php

/**
 * GO! V1 — Deploy Sonrası Temizlik Betiği
 *
 * .cpanel.yml tarafından otomatik çağrılır.
 * - Cache dosyalarını temizler
 * - Deploy logunu kaydeder
 * - Temel sağlık kontrolü yapar
 */

declare(strict_types=1);

define('GO_ROOT', dirname(__DIR__));

// Sadece CLI veya web üzerinden authorized çağrı
if (php_sapi_name() !== 'cli') {
    // Web üzerinden erişimi engelle
    header('HTTP/1.0 403 Forbidden');
    exit('Bu betik yalnızca CLI üzerinden çalıştırılabilir.');
}

$logFile  = GO_ROOT . '/storage/logs/deploy.log';
$cacheDir = GO_ROOT . '/storage/cache';

// ─── Loglama yardımcısı ───────────────────────────────────────────────────────

function deployLog(string $message): void
{
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    echo $line;
}

// ─── Başla ───────────────────────────────────────────────────────────────────

deployLog('=== Post-deploy başladı ===');

// 1. Cache temizle
$cleared = 0;
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.cache.json') ?: [];
    $files = array_merge($files, glob($cacheDir . '/ratelimit/*.rl.json') ?: []);
    foreach ($files as $file) {
        if (@unlink($file)) {
            $cleared++;
        }
    }
}
deployLog("Cache temizlendi: {$cleared} dosya silindi.");

// 2. Depolama dizinlerinin var olduğundan emin ol
$dirs = [
    GO_ROOT . '/storage/logs',
    GO_ROOT . '/storage/cache',
    GO_ROOT . '/storage/backups',
    GO_ROOT . '/storage/exports',
    GO_ROOT . '/storage/sandbox',
    GO_ROOT . '/uploads',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        deployLog("Dizin oluşturuldu: {$dir}");
    }
}

// 3. .env varlık kontrolü
if (!file_exists(GO_ROOT . '/.env')) {
    deployLog('UYARI: .env dosyası bulunamadı! Uygulama çalışmayabilir.');
} else {
    deployLog('.env dosyası mevcut. OK');
}

// 4. installed.lock kontrolü
if (!file_exists(GO_ROOT . '/storage/installed.lock')) {
    deployLog('BİLGİ: installed.lock bulunamadı — kurulum henüz tamamlanmamış.');
} else {
    deployLog('Kurulum kilidi mevcut. OK');
}

// 5. Deploy versiyonu kaydet (git commit hash ile)
$gitHash = trim(shell_exec('git -C ' . escapeshellarg(GO_ROOT) . ' rev-parse --short HEAD 2>/dev/null') ?? '');
if ($gitHash) {
    deployLog("Deploy commit: {$gitHash}");
}

deployLog('=== Post-deploy tamamlandı: ' . date('Y-m-d H:i:s') . ' ===');
exit(0);
