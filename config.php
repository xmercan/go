<?php

/**
 * GO! — Yapılandırma
 * .env dosyasını okuyup sabitlere map eder.
 * Bu dosya kurulum sihirbazı (Aşama 2) tarafından doldurulur.
 * Doğrudan web erişimi .htaccess ile engellenmiştir.
 */

declare(strict_types=1);

// Env yükleyici
if (!class_exists('GO\Core\Env')) {
    require_once __DIR__ . '/core/Env.php';
}

\GO\Core\Env::load(__DIR__ . '/.env');

// ─── Uygulama ────────────────────────────────────────────────────────────────
define('APP_NAME',     \GO\Core\Env::get('APP_NAME', 'GO.NET.TR'));
define('APP_URL',      rtrim(\GO\Core\Env::get('APP_URL', 'http://localhost'), '/'));
define('APP_KEY',      \GO\Core\Env::get('APP_KEY', ''));
define('APP_ENV',      \GO\Core\Env::get('APP_ENV', 'production'));
define('APP_MODE',     \GO\Core\Env::get('APP_MODE', 'production'));
define('APP_DEBUG',    \GO\Core\Env::get('APP_DEBUG', 'false') === 'true');
define('APP_TIMEZONE', \GO\Core\Env::get('APP_TIMEZONE', 'Europe/Istanbul'));
define('APP_LOCALE',   \GO\Core\Env::get('APP_LOCALE', 'tr'));

// ─── Veritabanı ──────────────────────────────────────────────────────────────
define('DB_HOST',    \GO\Core\Env::get('DB_HOST', 'localhost'));
define('DB_PORT',    (int)\GO\Core\Env::get('DB_PORT', '3306'));
define('DB_NAME',    \GO\Core\Env::get('DB_NAME', ''));
define('DB_USER',    \GO\Core\Env::get('DB_USER', ''));
define('DB_PASS',    \GO\Core\Env::get('DB_PASS', ''));
define('DB_CHARSET', \GO\Core\Env::get('DB_CHARSET', 'utf8mb4'));

// ─── SMTP ────────────────────────────────────────────────────────────────────
define('SMTP_HOST',       \GO\Core\Env::get('SMTP_HOST', ''));
define('SMTP_PORT',       (int)\GO\Core\Env::get('SMTP_PORT', '587'));
define('SMTP_ENCRYPTION', \GO\Core\Env::get('SMTP_ENCRYPTION', 'tls'));
define('SMTP_USER',       \GO\Core\Env::get('SMTP_USER', ''));
define('SMTP_PASS',       \GO\Core\Env::get('SMTP_PASS', ''));
define('SMTP_FROM_EMAIL', \GO\Core\Env::get('SMTP_FROM_EMAIL', ''));
define('SMTP_FROM_NAME',  \GO\Core\Env::get('SMTP_FROM_NAME', 'GO!'));

// ─── Storage ─────────────────────────────────────────────────────────────────
define('STORAGE_DRIVER',     \GO\Core\Env::get('STORAGE_DRIVER', 'local'));
define('STORAGE_LOCAL_PATH', GO_ROOT . '/' . trim(\GO\Core\Env::get('STORAGE_LOCAL_PATH', 'storage/app'), '/'));

// ─── AI ──────────────────────────────────────────────────────────────────────
define('AI_ENABLED',  \GO\Core\Env::get('AI_ENABLED', 'false') === 'true');
define('AI_PROVIDER', \GO\Core\Env::get('AI_PROVIDER', ''));
define('AI_MODEL',    \GO\Core\Env::get('AI_MODEL', ''));
define('AI_API_KEY',  \GO\Core\Env::get('AI_API_KEY', ''));

// ─── Session ─────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME', (int)\GO\Core\Env::get('SESSION_LIFETIME', '120'));

// ─── Queue ───────────────────────────────────────────────────────────────────
define('QUEUE_DRIVER',      \GO\Core\Env::get('QUEUE_DRIVER', 'database'));
define('QUEUE_RETRY_AFTER', (int)\GO\Core\Env::get('QUEUE_RETRY_AFTER', '90'));

// ─── Cache ───────────────────────────────────────────────────────────────────
define('CACHE_DRIVER',        \GO\Core\Env::get('CACHE_DRIVER', 'file'));
define('CACHE_TTL_SETTINGS',  (int)\GO\Core\Env::get('CACHE_TTL_SETTINGS', '3600'));
define('CACHE_TTL_SECTORS',   (int)\GO\Core\Env::get('CACHE_TTL_SECTORS', '3600'));
define('CACHE_TTL_DASHBOARD', (int)\GO\Core\Env::get('CACHE_TTL_DASHBOARD', '300'));

// ─── Yollar ──────────────────────────────────────────────────────────────────
define('STORAGE_PATH',   GO_ROOT . '/storage');
define('CACHE_PATH',     GO_ROOT . '/storage/cache');
define('LOG_PATH',       GO_ROOT . '/storage/logs');
define('SANDBOX_PATH',   GO_ROOT . '/storage/sandbox');
define('EXPORT_PATH',    GO_ROOT . '/storage/exports');
define('UPLOAD_PATH',    GO_ROOT . '/uploads');
define('DATABASE_PATH',  GO_ROOT . '/database');
