<?php

namespace GO\Core;

/**
 * .env dosyasını okur ve parse eder.
 * Composer gerektirmez. Saf PHP implementasyonu.
 */
class Env
{
    private static array $data = [];
    private static bool  $loaded = false;

    /**
     * .env dosyasını yükle.
     */
    public static function load(string $path): void
    {
        if (self::$loaded || !file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Yorum satırları (#) ve boş satırları atla
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // KEY=VALUE formatı
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Tırnak işaretlerini temizle (" veya ')
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[-1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            // Boş anahtar yok
            if ($key === '') {
                continue;
            }

            self::$data[$key] = $value;

            // putenv + $_ENV + $_SERVER (opsiyonel uyumluluk)
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Değer al.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$data[$key] ?? $_ENV[$key] ?? $default;
    }

    /**
     * Değer var mı?
     */
    public static function has(string $key): bool
    {
        return isset(self::$data[$key]) || isset($_ENV[$key]);
    }

    /**
     * Tüm değerleri al (debug için).
     */
    public static function all(): array
    {
        return self::$data;
    }
}
