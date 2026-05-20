<?php

namespace GO\Integrations\Storage;

/**
 * Local filesystem storage driver.
 * V1 varsayılan. Tüm dosyalar storage/app/ altında.
 */
class LocalStorage implements StorageInterface
{
    private string $root;

    public function __construct()
    {
        $this->root = defined('STORAGE_LOCAL_PATH')
            ? STORAGE_LOCAL_PATH
            : GO_ROOT . '/storage/app';
    }

    public function put(string $path, string $contents): bool
    {
        $fullPath = $this->fullPath($path);
        $this->makeDirectory(dirname($path));
        return file_put_contents($fullPath, $contents, LOCK_EX) !== false;
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->fullPath($path);
        if (!file_exists($fullPath)) {
            return null;
        }
        $content = file_get_contents($fullPath);
        return $content !== false ? $content : null;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->fullPath($path));
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->fullPath($path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    public function makeDirectory(string $path): bool
    {
        $fullPath = $this->fullPath($path);
        if (!is_dir($fullPath)) {
            return mkdir($fullPath, 0755, true);
        }
        return true;
    }

    public function files(string $directory): array
    {
        $fullPath = $this->fullPath($directory);
        if (!is_dir($fullPath)) {
            return [];
        }

        $files = [];
        $items = scandir($fullPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $fullItem = $fullPath . '/' . $item;
            if (is_file($fullItem)) {
                $files[] = [
                    'name'     => $item,
                    'path'     => ltrim($directory . '/' . $item, '/'),
                    'size'     => filesize($fullItem),
                    'modified' => filemtime($fullItem),
                ];
            }
        }

        return $files;
    }

    public function copy(string $from, string $to): bool
    {
        $this->makeDirectory(dirname($to));
        return copy($this->fullPath($from), $this->fullPath($to));
    }

    public function url(string $path): string
    {
        $baseUrl = defined('APP_URL') ? APP_URL : '';
        // public/ altındaki dosyalar için URL döner
        // Özel alanlar (sandbox, exports) için controller üzerinden gidilmeli
        return $baseUrl . '/storage-public/' . ltrim($path, '/');
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function fullPath(string $path): string
    {
        return rtrim($this->root, '/') . '/' . ltrim($path, '/');
    }
}
