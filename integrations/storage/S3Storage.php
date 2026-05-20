<?php

namespace GO\Integrations\Storage;

/**
 * Amazon S3 / Cloudflare R2 / Wasabi storage driver.
 * V2 için hazır stub. V1'de kullanılmaz.
 *
 * V2 uygulama notu:
 * - AWS SDK Composer olmadan kullanılamaz.
 * - V2'de Composer zorunlu hale gelebilir VEYA
 *   native HTTP + AWS Signature v4 implementasyonu eklenebilir.
 */
class S3Storage implements StorageInterface
{
    public function __construct()
    {
        throw new \RuntimeException(
            'S3Storage V2\'de implement edilecek. V1\'de STORAGE_DRIVER=local kullanın.'
        );
    }

    public function put(string $path, string $contents): bool
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }

    public function get(string $path): ?string
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }

    public function exists(string $path): bool
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }

    public function delete(string $path): bool
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }

    public function makeDirectory(string $path): bool
    {
        return true; // S3'te dizin kavramı yok
    }

    public function files(string $directory): array
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }

    public function copy(string $from, string $to): bool
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }

    public function url(string $path): string
    {
        throw new \RuntimeException('S3Storage V2 stub.');
    }
}
