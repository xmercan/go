<?php

/**
 * Uygulama geneli yardımcı fonksiyonlar.
 * Settings tablosundan değer okuma, debug, vb.
 */

if (!function_exists('get_setting')) {
    /**
     * Settings tablosundan değer al.
     *
     * @param string $key      Setting anahtarı
     * @param mixed  $default  Bulunamazsa dönecek değer
     * @return mixed
     */
    function get_setting(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $pdo  = \GO\Core\Database::connection();
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
            $cache[$key] = $row !== false ? $row['setting_value'] : $default;
        } catch (\Throwable) {
            $cache[$key] = $default;
        }

        return $cache[$key];
    }
}

if (!function_exists('set_setting')) {
    /**
     * Settings tablosuna değer yaz (INSERT OR UPDATE).
     */
    function set_setting(string $key, string $value): bool
    {
        try {
            $pdo  = \GO\Core\Database::connection();
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");
            $stmt->execute([$key, $value]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}

if (!function_exists('app_url')) {
    /**
     * APP_URL sabitini güvenli döndür.
     */
    function app_url(string $path = ''): string
    {
        $base = defined('APP_URL') ? rtrim(APP_URL, '/') : '';
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    /**
     * Statik dosya yolu üret.
     * Kullanım: asset('css/app.css') → https://go.net.tr/css/app.css
     */
    function asset(string $path): string
    {
        return app_url($path);
    }
}

if (!function_exists('abort')) {
    /**
     * HTTP hata kodu ile çık.
     */
    function abort(int $code = 404, string $message = ''): void
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}
