<?php

/**
 * Adım 2: Veritabanı Bağlantısı
 */

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $port = (int)($_POST['db_port'] ?? 3306);
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';

    if (empty($name) || empty($user)) {
        $error = 'Veritabanı adı ve kullanıcı adı zorunludur.';
    } else {
        // Bağlantı testi
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Session'a kaydet
            $_SESSION['db_host'] = $host;
            $_SESSION['db_port'] = $port;
            $_SESSION['db_name'] = $name;
            $_SESSION['db_user'] = $user;
            $_SESSION['db_pass'] = $pass;

            $_SESSION['install_completed'][] = 2;
            header('Location: /install/?step=3');
            exit;
        } catch (PDOException $e) {
            $error = 'Bağlantı hatası: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

install_head('Veritabanı Bağlantısı', $step, $stepTitles);
?>

<div class="alert alert-info">
    cPanel → MySQL Databases bölümünden veritabanı, kullanıcı ve yetki oluşturun. Bilgileri aşağıya girin.
</div>

<?php if ($error): ?>
<div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label for="db_host">Veritabanı Sunucusu</label>
        <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost', ENT_QUOTES) ?>" required>
        <small class="hint">Genellikle "localhost" veya "127.0.0.1"</small>
    </div>
    <div class="form-group">
        <label for="db_port">Port</label>
        <input type="number" id="db_port" name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306', ENT_QUOTES) ?>">
    </div>
    <div class="form-group">
        <label for="db_name">Veritabanı Adı</label>
        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '', ENT_QUOTES) ?>" required placeholder="ornekuser_goveri">
    </div>
    <div class="form-group">
        <label for="db_user">Kullanıcı Adı</label>
        <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '', ENT_QUOTES) ?>" required>
    </div>
    <div class="form-group">
        <label for="db_pass">Şifre</label>
        <input type="password" id="db_pass" name="db_pass" value="">
    </div>
    <button type="submit" class="btn btn-primary btn-full">Bağlantıyı Test Et ve Devam Et →</button>
</form>

<?php
install_foot();
