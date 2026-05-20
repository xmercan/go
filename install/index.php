<?php

/**
 * GO! Kurulum Sihirbazı — Ana Router
 * 6 adımlı kurulum; tamamlandıktan sonra install.lock ile kilitleniyor.
 */

declare(strict_types=1);

// Kurulum sırasında hataları göster (canlıya geçince complete.php kaldırır bunu)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Install kilit kontrolü
$lockFile = dirname(__DIR__) . '/storage/installed.lock';
if (file_exists($lockFile)) {
    header('HTTP/1.1 403 Forbidden');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>GO!</title>
    <style>body{background:#0A1628;color:#fff;font-family:system-ui;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center;}
    h1{color:#00D4FF;}p{opacity:.7;}</style></head><body>
    <div><h1>GO!</h1><p>Kurulum zaten tamamlanmış.</p><p><a href="/" style="color:#00D4FF;">Ana Sayfaya Git</a></p></div>
    </body></html>';
    exit;
}

define('GO_ROOT',    dirname(__DIR__));
define('INSTALL_ROOT', __DIR__);
define('GO_START',   microtime(true));

session_name('GO_INSTALL');
session_start();

// Adım sıfırlama (örn: SQL import'u tekrar çalıştırmak için)
if (isset($_GET['reset'])) {
    $resetStep = (int)$_GET['reset'];
    if ($resetStep >= 1 && $resetStep <= 5) {
        $completed = $_SESSION['install_completed'] ?? [];
        $_SESSION['install_completed'] = array_values(array_filter($completed, fn($s) => $s < $resetStep));
        unset($_SESSION['db_imported']);
    }
    header('Location: /install/?step=' . $resetStep);
    exit;
}

// Step yönetimi
$step = (int)($_GET['step'] ?? $_SESSION['install_step'] ?? 1);
$step = max(1, min(6, $step));

// Step geçerlilik: önceki adımlar tamamlanmadan ileri gidilemez
$completed = $_SESSION['install_completed'] ?? [];
if ($step > 1 && !in_array($step - 1, $completed, true)) {
    $step = max(1, min(array_merge([0], $completed)) + 1);
    if (!in_array($step - 1, $completed, true) && $step > 1) {
        $step = 1;
    }
}

// Step dosyası
$stepFiles = [
    1 => 'requirements.php',
    2 => 'database.php',
    3 => 'import.php',
    4 => 'admin.php',
    5 => 'settings.php',
    6 => 'complete.php',
];

$stepTitles = [
    1 => 'Sistem Gereksinimleri',
    2 => 'Veritabanı Bağlantısı',
    3 => 'SQL İçe Aktarma',
    4 => 'Admin Hesabı',
    5 => 'Site Ayarları',
    6 => 'Kurulum Tamamlandı',
];

$stepFile = $step === 6
    ? INSTALL_ROOT . '/complete.php'
    : INSTALL_ROOT . '/steps/' . ($stepFiles[$step] ?? 'requirements.php');

// Layout başlık HTML
function install_head(string $title, int $step, array $stepTitles): void
{
    $totalSteps = 6;
    echo '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ' — GO! Kurulum</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0A1628; --card: rgba(255,255,255,0.04); --border: rgba(255,255,255,0.1);
            --blue: #00D4FF; --green: #22C55E; --orange: #F97316; --red: #EF4444;
            --text: #fff; --muted: rgba(255,255,255,0.55);
        }
        body { background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, sans-serif;
               min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 2rem 1rem; }
        .logo { font-size: 2rem; font-weight: 900; color: var(--blue); letter-spacing: -1px; margin-bottom: .25rem; }
        .logo-sub { font-size: .8rem; color: var(--muted); margin-bottom: 2rem; }
        .steps { display: flex; gap: .5rem; margin-bottom: 2rem; flex-wrap: wrap; justify-content: center; }
        .step-item { display: flex; align-items: center; gap: .4rem; font-size: .75rem; color: var(--muted); }
        .step-item.active { color: var(--blue); font-weight: 600; }
        .step-item.done   { color: var(--green); }
        .step-num { width: 24px; height: 24px; border-radius: 50%; background: var(--border); display: flex;
                    align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; flex-shrink: 0; }
        .step-item.active .step-num { background: var(--blue); color: var(--bg); }
        .step-item.done   .step-num { background: var(--green); color: var(--bg); }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 16px;
                padding: 2rem; width: 100%; max-width: 600px; backdrop-filter: blur(10px); }
        h2 { font-size: 1.3rem; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; font-size: .85rem; color: var(--muted); margin-bottom: .4rem; }
        input, select { width: 100%; background: rgba(255,255,255,0.06); border: 1px solid var(--border);
                        border-radius: 8px; padding: .7rem 1rem; color: var(--text); font-size: .95rem;
                        outline: none; transition: border .2s; }
        input:focus, select:focus { border-color: var(--blue); }
        input[type="password"] { letter-spacing: .1em; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
               padding: .8rem 2rem; border-radius: 8px; font-size: .95rem; font-weight: 600;
               cursor: pointer; border: none; transition: opacity .2s; text-decoration: none; }
        .btn-primary { background: var(--blue); color: var(--bg); }
        .btn-primary:hover { opacity: .85; }
        .btn-success { background: var(--green); color: var(--bg); }
        .btn-full { width: 100%; }
        .alert { padding: .9rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: .9rem; }
        .alert-error   { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }
        .alert-success { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.3); color: #86efac; }
        .alert-info    { background: rgba(0,212,255,.1);  border: 1px solid rgba(0,212,255,.2); color: #67e8f9; }
        .check-list { list-style: none; }
        .check-list li { display: flex; align-items: center; gap: .6rem; padding: .5rem 0;
                         border-bottom: 1px solid var(--border); font-size: .9rem; }
        .check-list li:last-child { border-bottom: none; }
        .badge-ok   { color: var(--green); font-weight: 700; }
        .badge-fail { color: var(--red);   font-weight: 700; }
        .badge-warn { color: var(--orange);font-weight: 700; }
        small.hint { display: block; font-size: .78rem; color: var(--muted); margin-top: .3rem; }
        .progress-bar { height: 4px; background: var(--border); border-radius: 2px; margin-bottom: 2rem; width: 100%; max-width: 600px; }
        .progress-bar-inner { height: 100%; background: var(--blue); border-radius: 2px; transition: width .3s; }
    </style>
</head>
<body>
    <div class="logo">GO!</div>
    <div class="logo-sub">Kurulum Sihirbazı</div>
    <div class="progress-bar">
        <div class="progress-bar-inner" style="width:' . round(($step / $totalSteps) * 100) . '%"></div>
    </div>
    <div class="steps">';

    foreach ($stepTitles as $s => $t) {
        $class = $s < $step ? 'done' : ($s === $step ? 'active' : '');
        $icon  = $s < $step ? '✓' : $s;
        echo '<div class="step-item ' . $class . '">
            <div class="step-num">' . $icon . '</div>
            <span>' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '</span>
        </div>';
        if ($s < $totalSteps) {
            echo '<span style="color:rgba(255,255,255,.2)">›</span>';
        }
    }

    echo '</div><div class="card"><h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
}

function install_foot(): void
{
    echo '</div></body></html>';
}

// Step dosyasını include et
if (file_exists($stepFile)) {
    require $stepFile;
} else {
    install_head('Adım Bulunamadı', $step, $stepTitles);
    echo '<div class="alert alert-error">Kurulum dosyası bulunamadı: ' . htmlspecialchars($stepFile, ENT_QUOTES, 'UTF-8') . '</div>';
    install_foot();
}
