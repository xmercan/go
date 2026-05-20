<?php

namespace GO\Services;

use GO\Integrations\Storage\StorageInterface;
use GO\Integrations\Storage\LocalStorage;

/**
 * Storage facade (disk seçici).
 * Tüm dosya işlemleri bu servis üzerinden yapılır.
 * Driver: .env STORAGE_DRIVER değerine göre seçilir.
 */
class StorageService
{
    private static ?StorageInterface $disk = null;

    /**
     * Aktif storage driver'ını al.
     */
    public static function disk(?string $driver = null): StorageInterface
    {
        if (self::$disk !== null && $driver === null) {
            return self::$disk;
        }

        $driver = $driver ?? (defined('STORAGE_DRIVER') ? STORAGE_DRIVER : 'local');

        self::$disk = match ($driver) {
            's3'        => new \GO\Integrations\Storage\S3Storage(),
            default     => new LocalStorage(),
        };

        return self::$disk;
    }

    /**
     * Reset (test veya driver değişimi için).
     */
    public static function reset(): void
    {
        self::$disk = null;
    }

    // ─── Kısayollar ───────────────────────────────────────────────────────────

    public static function put(string $path, string $contents): bool
    {
        return self::disk()->put($path, $contents);
    }

    public static function get(string $path): ?string
    {
        return self::disk()->get($path);
    }

    public static function exists(string $path): bool
    {
        return self::disk()->exists($path);
    }

    public static function delete(string $path): bool
    {
        return self::disk()->delete($path);
    }

    public static function url(string $path): string
    {
        return self::disk()->url($path);
    }

    /**
     * Sandbox klasörü — proje bazlı izole alan.
     */
    public static function sandboxPath(int $projectId, string $version = 'v1'): string
    {
        $base = defined('SANDBOX_PATH') ? SANDBOX_PATH : GO_ROOT . '/storage/sandbox';
        return $base . '/' . $projectId . '/' . $version;
    }

    /**
     * Sandbox path validation — path traversal önlemi.
     *
     * @throws \RuntimeException Güvensiz yol
     */
    public static function validateSandboxPath(int $projectId, string $relativePath): string
    {
        $base = realpath(self::sandboxPath($projectId));
        if ($base === false) {
            // Sandbox henüz yok
            $base = self::sandboxPath($projectId);
        }

        // relativePath içinde .. ve null byte yasaklı
        if (str_contains($relativePath, '..') || str_contains($relativePath, "\0")) {
            throw new \RuntimeException('Geçersiz dosya yolu.');
        }

        $full = realpath($base . '/' . ltrim($relativePath, '/'));

        // realpath sonucu sandbox içinde mi?
        if ($full === false || !str_starts_with($full, $base)) {
            throw new \RuntimeException('Geçersiz dosya yolu: sandbox dışına çıkılamaz.');
        }

        return $full;
    }
}
