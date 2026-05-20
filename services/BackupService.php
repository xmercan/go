<?php

namespace GO\Services;

/**
 * Yedekleme servisi.
 * DB dump (mysqldump veya PDO fallback) ve dosya yedeği (ZipArchive).
 * Yedekler storage/backups/ altında saklanır.
 * storage/backups/.htaccess ile dışarıdan erişime kapalıdır.
 */
class BackupService
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = (defined('GO_ROOT') ? GO_ROOT : dirname(__DIR__)) . '/storage/backups';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Veritabanı yedeği al.
     * mysqldump varsa kullanır, yoksa PDO ile SQL dump üretir.
     *
     * @return string Oluşturulan yedek dosyasının tam yolu
     * @throws \RuntimeException Yedek alınamazsa
     */
    public function databaseBackup(): string
    {
        $filename = $this->backupDir . '/db_' . date('Ymd_His') . '.sql';

        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $name = defined('DB_NAME') ? DB_NAME : '';
        $user = defined('DB_USER') ? DB_USER : '';
        $pass = defined('DB_PASS') ? DB_PASS : '';

        if (empty($name)) {
            throw new \RuntimeException('DB_NAME tanımlı değil.');
        }

        // mysqldump deneme
        if ($this->tryMysqldump($host, $name, $user, $pass, $filename)) {
            return $filename;
        }

        // PDO fallback
        $this->pdoDump($filename);
        return $filename;
    }

    /**
     * Uygulama dosyaları yedeği al (ZipArchive).
     *
     * @param array $exclude Hariç tutulacak yollar (GO_ROOT'a göreli)
     * @return string Oluşturulan zip dosyasının tam yolu
     * @throws \RuntimeException ZipArchive yoksa veya yazma başarısızsa
     */
    public function filesBackup(array $exclude = []): string
    {
        if (!class_exists('\ZipArchive')) {
            throw new \RuntimeException('ZipArchive PHP uzantısı bulunamadı.');
        }

        $root     = defined('GO_ROOT') ? GO_ROOT : dirname(__DIR__);
        $filename = $this->backupDir . '/files_' . date('Ymd_His') . '.zip';

        $defaultExclude = [
            'storage/backups',
            'storage/cache',
            'node_modules',
            '.git',
        ];

        $excludeDirs = array_merge($defaultExclude, $exclude);

        $zip = new \ZipArchive();
        if ($zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Zip dosyası oluşturulamadı: {$filename}");
        }

        $this->addDirToZip($zip, $root, $root, $excludeDirs);
        $zip->close();

        if (!file_exists($filename)) {
            throw new \RuntimeException("Zip dosyası oluşturulamadı.");
        }

        return $filename;
    }

    /**
     * Eski yedekleri temizle (en son N tanesini tut).
     */
    public function cleanup(int $keepLast = 10): int
    {
        $deleted = 0;

        foreach (['db_*.sql', 'files_*.zip'] as $pattern) {
            $files = glob($this->backupDir . '/' . $pattern) ?: [];
            sort($files);

            if (count($files) > $keepLast) {
                $toDelete = array_slice($files, 0, count($files) - $keepLast);
                foreach ($toDelete as $file) {
                    if (@unlink($file)) {
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    /**
     * Mevcut yedekleri listele.
     */
    public function list(): array
    {
        $backups = [];

        $files = glob($this->backupDir . '/*.{sql,zip}', GLOB_BRACE) ?: [];
        rsort($files);

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path'     => $file,
                'size'     => filesize($file),
                'size_mb'  => round(filesize($file) / 1024 / 1024, 2),
                'created'  => date('Y-m-d H:i:s', filemtime($file)),
                'type'     => str_ends_with($file, '.sql') ? 'database' : 'files',
            ];
        }

        return $backups;
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function tryMysqldump(string $host, string $name, string $user, string $pass, string $output): bool
    {
        $mysqldump = $this->findMysqldump();
        if (!$mysqldump) {
            return false;
        }

        $passArg = !empty($pass) ? '-p' . escapeshellarg($pass) : '';
        $cmd = sprintf(
            '%s -h %s -u %s %s --single-transaction --skip-lock-tables --routines %s > %s 2>/dev/null',
            escapeshellcmd($mysqldump),
            escapeshellarg($host),
            escapeshellarg($user),
            $passArg,
            escapeshellarg($name),
            escapeshellarg($output)
        );

        exec($cmd, $out, $exitCode);

        return $exitCode === 0 && file_exists($output) && filesize($output) > 100;
    }

    private function findMysqldump(): ?string
    {
        $paths = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/usr/mysql/bin/mysqldump'];
        foreach ($paths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }
        // which komutu ile bul
        $found = trim((string)shell_exec('which mysqldump 2>/dev/null'));
        return $found ?: null;
    }

    private function pdoDump(string $output): void
    {
        $pdo = \GO\Core\Database::connection();

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $sql    = "-- GO! Database Backup\n-- " . date('Y-m-d H:i:s') . "\n-- Generated by BackupService (PDO fallback)\n\n";
        $sql   .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            // CREATE TABLE
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= ($createRow['Create Table'] ?? '') . ";\n\n";

            // INSERT DATA
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
                foreach ($rows as $row) {
                    $vals = array_map(function ($v) use ($pdo) {
                        return $v === null ? 'NULL' : $pdo->quote((string)$v);
                    }, array_values($row));
                    $sql .= "INSERT INTO `{$table}` ({$cols}) VALUES (" . implode(', ', $vals) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        if (file_put_contents($output, $sql) === false) {
            throw new \RuntimeException("Yedek dosyası yazılamadı: {$output}");
        }
    }

    private function addDirToZip(\ZipArchive $zip, string $dir, string $root, array $excludeDirs): void
    {
        $items = @scandir($dir);
        if (!$items) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath    = $dir . '/' . $item;
            $relativePath = ltrim(str_replace($root, '', $fullPath), '/');

            // Hariç tutulacak kontrolü
            $excluded = false;
            foreach ($excludeDirs as $excl) {
                if (str_starts_with($relativePath, $excl)) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) {
                continue;
            }

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($relativePath);
                $this->addDirToZip($zip, $fullPath, $root, $excludeDirs);
            } else {
                $zip->addFile($fullPath, $relativePath);
            }
        }
    }
}
