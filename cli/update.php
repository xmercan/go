#!/usr/bin/env php
<?php

/**
 * GO! V1 — Update / Migration CLI Aracı
 *
 * Kullanım:
 *   php cli/update.php --check          Mevcut DB versiyonunu göster
 *   php cli/update.php --list           Uygulanmış migration'ları listele
 *   php cli/update.php --pending        Uygulanmamış migration'ları listele
 *   php cli/update.php --run            Bekleyen tüm migration'ları uygula
 *   php cli/update.php --run --dry-run  Neyin çalışacağını göster (uygulamadan)
 *   php cli/update.php --version=1.0.1  Belirli bir versiyonu uygula
 */

declare(strict_types=1);

define('GO_ROOT',  dirname(__DIR__));
define('GO_START', microtime(true));

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Bu araç yalnızca CLI üzerinden çalıştırılabilir.\n");
}

// ─── Ortam ────────────────────────────────────────────────────────────────────

require GO_ROOT . '/core/Env.php';
\GO\Core\Env::load(GO_ROOT . '/.env');

require GO_ROOT . '/config.php';
require GO_ROOT . '/core/Database.php';

$options = getopt('', ['check', 'list', 'pending', 'run', 'dry-run', 'version:', 'help', 'no-backup']);

if (isset($options['help']) || empty($options)) {
    echo "GO! V1 — Update CLI\n\n";
    echo "Kullanım:\n";
    echo "  php cli/update.php --check              Mevcut DB versiyonunu göster\n";
    echo "  php cli/update.php --list               Uygulanmış migration'ları listele\n";
    echo "  php cli/update.php --pending            Uygulanmamış migration'ları listele\n";
    echo "  php cli/update.php --run                Bekleyen tüm migration'ları uygula\n";
    echo "  php cli/update.php --run --dry-run      Neyin uygulanacağını göster\n";
    echo "  php cli/update.php --run --no-backup    Backup almadan uygula\n";
    echo "  php cli/update.php --version=1.0.1      Belirli versiyonu uygula\n\n";
    echo "Updates klasörü: " . GO_ROOT . "/updates/\n";
    echo "Migrations tablosu: schema_migrations\n";
    exit(0);
}

// ─── DB Bağlantısı ───────────────────────────────────────────────────────────

try {
    $pdo = \GO\Core\Database::getInstance();
} catch (\Throwable $e) {
    die("DB bağlantı hatası: " . $e->getMessage() . "\n");
}

// ─── Loglama ─────────────────────────────────────────────────────────────────

$logFile = GO_ROOT . '/storage/logs/update.log';

function updateLog(string $msg, string $level = 'INFO'): void
{
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $msg . "\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    echo $line;
}

// ─── Yardımcı: Uygulanan migration'lar ───────────────────────────────────────

function getAppliedVersions(\PDO $pdo): array
{
    try {
        $rows = $pdo->query("SELECT version FROM schema_migrations ORDER BY applied_at ASC")
                    ->fetchAll(\PDO::FETCH_COLUMN);
        return $rows ?: [];
    } catch (\Throwable) {
        return [];
    }
}

// ─── Yardımcı: updates/ klasöründeki migration dosyaları ────────────────────

function getUpdateFiles(): array
{
    $dir   = GO_ROOT . '/updates';
    $files = [];

    if (!is_dir($dir)) {
        return [];
    }

    // SQL ve PHP migration dosyalarını topla, ada göre sırala
    $allFiles = glob($dir . '/v*.{sql,php}', GLOB_BRACE) ?: [];
    sort($allFiles);

    // Her dosyayı sürüm → dosyalar haritasına ekle
    $map = [];
    foreach ($allFiles as $file) {
        $base    = pathinfo($file, PATHINFO_FILENAME); // örn: v1_0_1
        $version = str_replace('_', '.', ltrim($base, 'v')); // örn: 1.0.1
        $map[$version][] = $file;
    }

    return $map;
}

// ─── Yardımcı: Tek migration uygula ─────────────────────────────────────────

function applyMigration(\PDO $pdo, string $version, array $files, bool $dryRun = false): bool
{
    updateLog("Uygulanıyor: v{$version}" . ($dryRun ? ' [DRY-RUN]' : ''));

    foreach ($files as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if ($ext === 'sql') {
            if ($dryRun) {
                updateLog("  [DRY-RUN] SQL çalıştırılacak: " . basename($file));
                continue;
            }

            $sql = file_get_contents($file);
            // Yorumları temizle
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            $sql = preg_replace('/--[^\n]*/', '', $sql);
            $sql = preg_replace('/#[^\n]*/', '', $sql);

            $statements = array_filter(array_map('trim', explode(';', $sql)));

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $errors = [];
            foreach ($statements as $stmt) {
                if (empty($stmt)) continue;
                try {
                    $pdo->exec($stmt);
                } catch (\PDOException $e) {
                    $errors[] = $e->getMessage();
                    updateLog("  HATA: " . $e->getMessage(), 'ERROR');
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            if (!empty($errors)) {
                updateLog("  UYARI: " . count($errors) . " statement başarısız.", 'WARN');
            } else {
                updateLog("  SQL OK: " . basename($file));
            }

        } elseif ($ext === 'php') {
            if ($dryRun) {
                updateLog("  [DRY-RUN] PHP migration çalıştırılacak: " . basename($file));
                continue;
            }

            try {
                $result = (function (\PDO $pdo, string $file): mixed {
                    return require $file;
                })($pdo, $file);

                if ($result === false) {
                    updateLog("  PHP migration false döndürdü: " . basename($file), 'ERROR');
                    return false;
                }
                updateLog("  PHP OK: " . basename($file));
            } catch (\Throwable $e) {
                updateLog("  PHP HATA: " . $e->getMessage(), 'ERROR');
                return false;
            }
        }
    }

    if (!$dryRun) {
        // schema_migrations'a kaydet
        $pdo->prepare("INSERT IGNORE INTO schema_migrations (version, applied_at) VALUES (?, NOW())")
            ->execute([$version]);

        // settings'teki app_version'ı güncelle
        $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('app_version', ?)
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()")
            ->execute([$version]);

        updateLog("  v{$version} schema_migrations'a kaydedildi.");
    }

    return true;
}

// ─── --check ─────────────────────────────────────────────────────────────────

if (isset($options['check'])) {
    try {
        $row = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'app_version'")->fetch(\PDO::FETCH_ASSOC);
        $ver = $row['setting_value'] ?? null;
        echo "Mevcut uygulama versiyonu: " . ($ver ?? 'belirsiz (settings tablosu boş)') . "\n";
    } catch (\Throwable $e) {
        echo "Versiyon okunamadı: " . $e->getMessage() . "\n";
    }
    exit(0);
}

// ─── --list ──────────────────────────────────────────────────────────────────

if (isset($options['list'])) {
    try {
        $rows = $pdo->query("SELECT version, applied_at FROM schema_migrations ORDER BY applied_at ASC")
                    ->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($rows)) {
            echo "Henüz migration uygulanmamış.\n";
        } else {
            echo "Uygulanmış migration'lar:\n";
            foreach ($rows as $r) {
                echo "  ✓ [{$r['applied_at']}] v{$r['version']}\n";
            }
        }
    } catch (\Throwable $e) {
        echo "Migration listesi okunamadı: " . $e->getMessage() . "\n";
    }
    exit(0);
}

// ─── --pending ───────────────────────────────────────────────────────────────

if (isset($options['pending'])) {
    $applied  = getAppliedVersions($pdo);
    $allFiles = getUpdateFiles();
    $pending  = array_diff(array_keys($allFiles), $applied);

    if (empty($pending)) {
        echo "Bekleyen migration yok. Sistem güncel.\n";
    } else {
        echo "Bekleyen migration'lar (" . count($pending) . "):\n";
        foreach ($pending as $ver) {
            $files = $allFiles[$ver];
            echo "  • v{$ver}  (" . implode(', ', array_map('basename', $files)) . ")\n";
        }
    }
    exit(0);
}

// ─── --run ───────────────────────────────────────────────────────────────────

if (isset($options['run'])) {
    $dryRun   = isset($options['dry-run']);
    $noBackup = isset($options['no-backup']);

    $applied  = getAppliedVersions($pdo);
    $allFiles = getUpdateFiles();
    $pending  = array_diff(array_keys($allFiles), $applied);

    if (empty($pending)) {
        echo "Bekleyen migration yok. Sistem güncel.\n";
        exit(0);
    }

    echo "Bekleyen " . count($pending) . " migration bulundu.\n";

    if ($dryRun) {
        echo "[DRY-RUN] Gerçek değişiklik yapılmayacak.\n\n";
    }

    // Backup al
    if (!$dryRun && !$noBackup) {
        updateLog("Güncelleme öncesi backup alınıyor...");
        try {
            require_once GO_ROOT . '/services/BackupService.php';
            $backup = new \GO\Services\BackupService();
            $backupFile = $backup->databaseBackup();
            updateLog("Backup alındı: {$backupFile}");
        } catch (\Throwable $e) {
            updateLog("UYARI: Backup alınamadı: " . $e->getMessage(), 'WARN');
            echo "Devam etmek istiyor musunuz? (backup alınamadı) [e/H]: ";
            $answer = strtolower(trim(fgets(STDIN) ?? ''));
            if ($answer !== 'e') {
                echo "İptal edildi.\n";
                exit(1);
            }
        }
    }

    // Migration'ları uygula
    $success = 0;
    $failed  = 0;

    foreach ($pending as $version) {
        $files = $allFiles[$version];
        $ok = applyMigration($pdo, $version, $files, $dryRun);
        if ($ok) {
            $success++;
        } else {
            $failed++;
            updateLog("Migration durduruluyor: v{$version} başarısız.", 'ERROR');
            break;
        }
    }

    echo "\n";
    echo "Sonuç: {$success} başarılı, {$failed} başarısız.\n";
    echo "Süre: " . round((microtime(true) - GO_START) * 1000) . "ms\n";

    exit($failed > 0 ? 1 : 0);
}

// ─── --version ───────────────────────────────────────────────────────────────

if (isset($options['version'])) {
    $version  = $options['version'];
    $allFiles = getUpdateFiles();

    if (!isset($allFiles[$version])) {
        // v{1_0_1} formatında da ara
        $altVersion = str_replace('.', '_', $version);
        echo "v{$version} için migration dosyası bulunamadı.\n";
        echo "Mevcut versiyonlar: " . implode(', ', array_keys($allFiles)) . "\n";
        exit(1);
    }

    $applied = getAppliedVersions($pdo);
    if (in_array($version, $applied, true)) {
        echo "v{$version} zaten uygulanmış.\n";
        exit(0);
    }

    $dryRun   = isset($options['dry-run']);
    $noBackup = isset($options['no-backup']);

    if (!$dryRun && !$noBackup) {
        updateLog("Güncelleme öncesi backup alınıyor...");
        try {
            require_once GO_ROOT . '/services/BackupService.php';
            $backup = new \GO\Services\BackupService();
            $backupFile = $backup->databaseBackup();
            updateLog("Backup alındı: {$backupFile}");
        } catch (\Throwable $e) {
            updateLog("UYARI: Backup alınamadı: " . $e->getMessage(), 'WARN');
        }
    }

    $ok = applyMigration($pdo, $version, $allFiles[$version], $dryRun);

    echo $ok ? "\nv{$version} başarıyla uygulandı. ✓\n" : "\nv{$version} uygulanamadı. Loglara bakın.\n";
    echo "Süre: " . round((microtime(true) - GO_START) * 1000) . "ms\n";

    exit($ok ? 0 : 1);
}

echo "Geçersiz komut. --help için yardım.\n";
exit(1);
