<?php

/**
 * Adım 3: SQL İçe Aktarma
 * SQL yorumları (tek satır ve cok satirli) once temizlenir,
 * sonra noktali virgülle bölünür. CREATE TABLE ifadeleri artık filtrelenmez.
 */

$error    = '';
$results  = [];
$success  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sqlFile = GO_ROOT . '/database/GoV1.sql';

    if (!file_exists($sqlFile)) {
        $error = 'GoV1.sql dosyası bulunamadı: ' . $sqlFile;
    } else {
        try {
            $host = $_SESSION['db_host'] ?? 'localhost';
            $port = (int)($_SESSION['db_port'] ?? 3306);
            $name = $_SESSION['db_name'] ?? '';
            $user = $_SESSION['db_user'] ?? '';
            $pass = $_SESSION['db_pass'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pdo->exec("SET NAMES utf8mb4");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("SET SQL_MODE = ''");

            $sql = file_get_contents($sqlFile);
            if ($sql === false) {
                throw new RuntimeException('GoV1.sql okunamadı.');
            }

            // ── Yorumları temizle ──────────────────────────────────────────────
            // 1) Çok satırlı yorumlar: /* ... */
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            // 2) Tek satırlı yorumlar: -- ... (satır sonu dahil)
            $sql = preg_replace('/--[^\n]*/', '', $sql);
            // 3) # ile başlayan yorumlar
            $sql = preg_replace('/#[^\n]*/', '', $sql);
            // ──────────────────────────────────────────────────────────────────

            // Noktalı virgülle böl ve boş ifadeleri at
            $rawStatements = explode(';', $sql);
            $statements = array_filter(
                array_map('trim', $rawStatements),
                fn($s) => $s !== ''
            );

            $executed = 0;
            $warnings = 0;
            $errors   = [];

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '') continue;
                try {
                    $pdo->exec($stmt);
                    $executed++;
                } catch (PDOException $e) {
                    $msg = $e->getMessage();
                    // Duplicate entry veya tablo/kolon zaten var uyarı say
                    if (
                        str_contains($msg, 'Duplicate entry') ||
                        str_contains($msg, 'already exists') ||
                        str_contains($msg, 'Duplicate key')
                    ) {
                        $warnings++;
                    } else {
                        $errors[] = $msg;
                    }
                }
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            // Kritik tabloların gerçekten oluştuğunu doğrula
            $criticalTables = ['admins', 'users', 'projects', 'settings', 'jobs'];
            $missingTables  = [];
            foreach ($criticalTables as $tbl) {
                $check = $pdo->query("SHOW TABLES LIKE '{$tbl}'")->fetchAll();
                if (empty($check)) {
                    $missingTables[] = $tbl;
                }
            }

            if (!empty($missingTables)) {
                $error = 'SQL import tamamlandı ancak kritik tablolar oluşturulamadı: '
                       . implode(', ', $missingTables)
                       . '. Lütfen veritabanı kullanıcısının CREATE TABLE yetkisi olduğundan emin olun ve tekrar deneyin.';
            } elseif (empty($errors)) {
                $results[] = [
                    'type' => 'ok',
                    'msg'  => "{$executed} SQL ifadesi başarıyla çalıştırıldı."
                              . ($warnings > 0 ? " ({$warnings} tekrar atlandı)" : '')
                              . " — 34 tablo hazır.",
                ];
                $_SESSION['install_completed'][] = 3;
                $_SESSION['db_imported']         = true;
                header('Location: /install/?step=4');
                exit;
            } else {
                $topErrors = array_slice($errors, 0, 5);
                $error = 'SQL hataları (' . count($errors) . ' adet):<br>'
                       . implode('<br>', array_map(fn($e) => '• ' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8'), $topErrors));
                if (count($errors) > 5) {
                    $error .= '<br>... ve ' . (count($errors) - 5) . ' hata daha.';
                }
            }

        } catch (PDOException $e) {
            $error = 'Veritabanı bağlantısı kurulamadı: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        } catch (RuntimeException $e) {
            $error = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

install_head('SQL İçe Aktarma', $step, $stepTitles);
?>

<div class="alert alert-info">
    <strong>GoV1.sql</strong> — 34 tablo veritabanınıza yüklenecek.
    Temiz bir veritabanı kullanmanız önerilir (mevcut veriler üzerine yazılabilir).
</div>

<?php if ($error): ?>
<div class="alert alert-error"><?= $error ?></div>
<a href="/install/?step=3&reset=3" class="btn btn-primary btn-full" style="margin-bottom:1rem">
    ↺ Tekrar Dene
</a>
<?php else: ?>
<?php foreach ($results as $r): ?>
<div class="alert alert-<?= $r['type'] === 'ok' ? 'success' : 'info' ?>">
    <?= htmlspecialchars($r['msg'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<p style="color:rgba(255,255,255,.6);font-size:.85rem;margin-bottom:1.5rem">
    Veritabanı: <strong><?= htmlspecialchars($_SESSION['db_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> &nbsp;|&nbsp;
    Sunucu: <strong><?= htmlspecialchars($_SESSION['db_host'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
</p>

<form method="POST">
    <button type="submit" class="btn btn-primary btn-full">SQL Yükle ve Devam Et →</button>
</form>

<?php
install_foot();
