<?php

namespace GO\Services;

/**
 * Dosya tabanlı cache servisi.
 * V1: local file cache. V2: Redis driver eklenebilir.
 */
class CacheService
{
    private string $cachePath;

    public function __construct()
    {
        $this->cachePath = defined('CACHE_PATH') ? CACHE_PATH : GO_ROOT . '/storage/cache';
    }

    /**
     * Cache'den değer al.
     * Süresi dolmuşsa null döner.
     */
    public function get(string $key): mixed
    {
        $file = $this->filePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return null;
        }

        // TTL kontrolü
        if ($data['expires_at'] !== null && time() > $data['expires_at']) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    /**
     * Cache'e değer yaz.
     *
     * @param string   $key
     * @param mixed    $value
     * @param int|null $ttl  Saniye. null = sonsuz
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->ensureDirectory();

        $data = [
            'key'        => $key,
            'value'      => $value,
            'created_at' => time(),
            'expires_at' => $ttl !== null ? time() + $ttl : null,
        ];

        return (bool)file_put_contents(
            $this->filePath($key),
            json_encode($data, JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    /**
     * Cache'de var mı ve süresi geçmemiş mi?
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Cache sil.
     */
    public function delete(string $key): bool
    {
        $file = $this->filePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Tüm cache temizle.
     */
    public function flush(): void
    {
        $files = glob($this->cachePath . '/*.cache.json');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Cache yoksa üret, varsa getir. (remember pattern)
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Settings cache key.
     */
    public static function keySettings(): string
    {
        return 'settings.all';
    }

    /**
     * Sektörler cache key.
     */
    public static function keySectors(): string
    {
        return 'sectors.active';
    }

    /**
     * Dashboard cache key.
     */
    public static function keyDashboard(string $scope, mixed $id = null): string
    {
        return 'dashboard.' . $scope . ($id ? '.' . $id : '') . '.' . date('Y-m-d');
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function filePath(string $key): string
    {
        // Güvenli dosya adı
        $safeKey = preg_replace('/[^a-zA-Z0-9._-]/', '_', $key);
        return $this->cachePath . '/' . $safeKey . '.cache.json';
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
}
