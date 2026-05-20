<?php

/**
 * Adım 4: Admin Hesabı Oluşturma
 */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirmation'] ?? '';

    $errors = [];
    if (strlen($fullName) < 3)                    $errors[] = 'Ad Soyad en az 3 karakter olmalı.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta girin.';
    if (strlen($password) < 8)                    $errors[] = 'Şifre en az 8 karakter olmalı.';
    if ($password !== $confirm)                   $errors[] = 'Şifreler eşleşmiyor.';

    if (empty($errors)) {
        try {
            $host = $_SESSION['db_host'] ?? 'localhost';
            $port = (int)($_SESSION['db_port'] ?? 3306);
            $name = $_SESSION['db_name'] ?? '';
            $user = $_SESSION['db_user'] ?? '';
            $pass = $_SESSION['db_pass'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $check = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'Bu e-posta ile zaten bir admin var.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $now    = date('Y-m-d H:i:s');

                $stmt = $pdo->prepare("
                    INSERT INTO admins (full_name, email, phone, password, role, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 'super_admin', 'active', ?, ?)
                ");
                $stmt->execute([$fullName, $email, $phone, $hashed, $now, $now]);

                $_SESSION['admin_full_name']      = $fullName;
                $_SESSION['admin_email']          = $email;
                $_SESSION['install_completed'][]  = 4;
                header('Location: /install/?step=5');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }

    $error = implode('<br>', array_map(
        fn($e) => htmlspecialchars($e, ENT_QUOTES, 'UTF-8'),
        $errors
    ));
}

// admins tablosunun varlığını doğrula
$tableOk = false;
try {
    $host = $_SESSION['db_host'] ?? 'localhost';
    $port = (int)($_SESSION['db_port'] ?? 3306);
    $name = $_SESSION['db_name'] ?? '';
    $user = $_SESSION['db_user'] ?? '';
    $pass = $_SESSION['db_pass'] ?? '';
    if ($name !== '' && $user !== '') {
        $tmpPdo  = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $tableOk = (bool) $tmpPdo->query("SHOW TABLES LIKE 'admins'")->fetch();
    }
} catch (Throwable $e) {
    $tableOk = false;
}

install_head('Admin Hesabı', $step, $stepTitles);
?>

<?php if (!$tableOk): ?>

<div class="alert alert-error">
    <strong>Veritabanı hazır değil!</strong><br>
    <code>admins</code> tablosu bulunamadı — SQL import adımı başarısız olmuş olabilir.
</div>
<a href="/install/?reset=3" class="btn btn-primary btn-full">
    ← Adım 3'e Dön: SQL Tekrar Yükle
</a>

<?php else: ?>

<div class="alert alert-info">
    Sisteme giriş yapacak ilk süper admin hesabını oluşturun. Şifre güvenli bir şekilde hash'lenecek.
</div>

<?php if ($error): ?>
<div class="alert alert-error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Ad Soyad</label>
        <input type="text" name="full_name"
               value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required placeholder="Adınız Soyadınız">
    </div>
    <div class="form-group">
        <label>E-posta</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required placeholder="admin@go.net.tr">
    </div>
    <div class="form-group">
        <label>Telefon</label>
        <input type="tel" name="phone"
               value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="05xx xxx xxxx">
    </div>
    <div class="form-group">
        <label>Şifre <small class="hint">En az 8 karakter</small></label>
        <input type="password" name="password" required autocomplete="new-password">
    </div>
    <div class="form-group">
        <label>Şifre Tekrar</label>
        <input type="password" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn btn-primary btn-full">Admin Oluştur ve Devam Et →</button>
</form>

<?php endif; ?>

<?php
install_foot();
