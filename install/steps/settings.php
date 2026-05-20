<?php

/**
 * Adım 5: Site Ayarları + SMTP + .env / config.php üretimi
 */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName  = trim($_POST['site_name'] ?? 'GO.NET.TR');
    $siteUrl   = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $smtpHost  = trim($_POST['smtp_host'] ?? '');
    $smtpPort  = (int)($_POST['smtp_port'] ?? 587);
    $smtpEnc   = in_array($_POST['smtp_encryption'] ?? 'tls', ['tls','ssl','none']) ? $_POST['smtp_encryption'] : 'tls';
    $smtpUser  = trim($_POST['smtp_user'] ?? '');
    $smtpPass  = $_POST['smtp_pass'] ?? '';
    $smtpFrom  = trim($_POST['smtp_from_email'] ?? $smtpUser);
    $smtpName  = trim($_POST['smtp_from_name'] ?? 'GO!');

    if (empty($siteUrl)) {
        $error = 'Site URL zorunludur.';
    } else {
        try {
            $host    = $_SESSION['db_host'] ?? 'localhost';
            $port    = (int)($_SESSION['db_port'] ?? 3306);
            $dbName  = $_SESSION['db_name'] ?? '';
            $dbUser  = $_SESSION['db_user'] ?? '';
            $dbPass  = $_SESSION['db_pass'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Settings güncelle
            $settingsUpdate = [
                'site_name' => $siteName,
                'site_url'  => $siteUrl,
            ];
            foreach ($settingsUpdate as $k => $v) {
                $st = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $st->execute([$v, $k]);
            }

            // SMTP settings
            if (!empty($smtpHost)) {
                $st = $pdo->prepare("
                    INSERT INTO smtp_settings (host, port, encryption, username, password, from_email, from_name, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE host=VALUES(host), port=VALUES(port),
                        encryption=VALUES(encryption), username=VALUES(username),
                        password=VALUES(password), from_email=VALUES(from_email), from_name=VALUES(from_name)
                ");
                $st->execute([$smtpHost, $smtpPort, $smtpEnc, $smtpUser, $smtpPass, $smtpFrom, $smtpName]);
            }

            // APP_KEY üret
            $appKey = bin2hex(random_bytes(32));

            // .env dosyası yaz
            $envContent = <<<ENV
# GO! V1 — Yapılandırma
# Oluşturulma: {$_SERVER['REQUEST_TIME']}

APP_NAME="{$siteName}"
APP_URL={$siteUrl}
APP_KEY={$appKey}
APP_ENV=production
APP_MODE=production
APP_DEBUG=false
APP_TIMEZONE=Europe/Istanbul
APP_LOCALE=tr

DB_HOST={$host}
DB_PORT={$port}
DB_NAME={$dbName}
DB_USER={$dbUser}
DB_PASS={$dbPass}
DB_CHARSET=utf8mb4

SMTP_HOST={$smtpHost}
SMTP_PORT={$smtpPort}
SMTP_ENCRYPTION={$smtpEnc}
SMTP_USER={$smtpUser}
SMTP_PASS={$smtpPass}
SMTP_FROM_EMAIL={$smtpFrom}
SMTP_FROM_NAME="{$smtpName}"

STORAGE_DRIVER=local
STORAGE_LOCAL_PATH=storage/app

AI_ENABLED=false
AI_PROVIDER=
AI_MODEL=
AI_API_KEY=

SESSION_LIFETIME=120
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Lax

QUEUE_DRIVER=database
QUEUE_RETRY_AFTER=90

CACHE_DRIVER=file
CACHE_TTL_SETTINGS=3600
CACHE_TTL_SECTORS=3600
CACHE_TTL_DASHBOARD=300
ENV;

            file_put_contents(GO_ROOT . '/.env', $envContent);

            $_SESSION['install_completed'][] = 5;
            $_SESSION['site_url']   = $siteUrl;
            $_SESSION['app_key']    = $appKey;
            header('Location: /install/?step=6');
            exit;

        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}

// Mevcut URL otomatik doldur
$defaultUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

install_head('Site Ayarları', $step, $stepTitles);
?>

<div class="alert alert-info">
    SMTP bilgilerinizi doldurun; boş bırakırsanız e-posta bildirimleri çalışmaz (sonradan admin panelinden ekleyebilirsiniz).
</div>

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Site Adı</label>
        <input type="text" name="site_name" value="<?= htmlspecialchars($_POST['site_name'] ?? 'GO.NET.TR', ENT_QUOTES) ?>" required>
    </div>
    <div class="form-group">
        <label>Site URL (https:// ile)</label>
        <input type="url" name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? $defaultUrl, ENT_QUOTES) ?>" required placeholder="https://go.net.tr">
        <small class="hint">Sonuna / koymayın</small>
    </div>

    <hr style="border-color:rgba(255,255,255,.1);margin:1.5rem 0">
    <p style="font-size:.85rem;color:rgba(255,255,255,.5);margin-bottom:1rem">SMTP Ayarları (opsiyonel)</p>

    <div class="form-group">
        <label>SMTP Sunucu</label>
        <input type="text" name="smtp_host" value="<?= htmlspecialchars($_POST['smtp_host'] ?? '', ENT_QUOTES) ?>" placeholder="mail.go.net.tr">
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
            <label>Port</label>
            <input type="number" name="smtp_port" value="<?= htmlspecialchars($_POST['smtp_port'] ?? '587', ENT_QUOTES) ?>">
        </div>
        <div class="form-group">
            <label>Şifreleme</label>
            <select name="smtp_encryption">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="none">Yok</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label>SMTP Kullanıcı Adı</label>
        <input type="text" name="smtp_user" value="<?= htmlspecialchars($_POST['smtp_user'] ?? '', ENT_QUOTES) ?>">
    </div>
    <div class="form-group">
        <label>SMTP Şifre</label>
        <input type="password" name="smtp_pass">
    </div>
    <div class="form-group">
        <label>Gönderici E-posta</label>
        <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($_POST['smtp_from_email'] ?? '', ENT_QUOTES) ?>">
    </div>
    <div class="form-group">
        <label>Gönderici Adı</label>
        <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($_POST['smtp_from_name'] ?? 'GO!', ENT_QUOTES) ?>">
    </div>

    <button type="submit" class="btn btn-primary btn-full">Kaydet ve Kurulumu Tamamla →</button>
</form>

<?php
install_foot();
