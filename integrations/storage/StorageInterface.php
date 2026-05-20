<?php

namespace GO\Integrations\Storage;

/**
 * Storage driver arayüzü.
 * V1: LocalStorage. V2: S3Storage, R2Storage, WasabiStorage.
 */
interface StorageInterface
{
    /**
     * Dosya yaz.
     */
    public function put(string $path, string $contents): bool;

    /**
     * Dosya oku.
     */
    public function get(string $path): ?string;

    /**
     * Dosya var mı?
     */
    public function exists(string $path): bool;

    /**
     * Dosya sil.
     */
    public function delete(string $path): bool;

    /**
     * Dizin oluştur.
     */
    public function makeDirectory(string $path): bool;

    /**
     * Dizindeki dosyaları listele.
     */
    public function files(string $directory): array;

    /**
     * Dosyayı kopyala.
     */
    public function copy(string $from, string $to): bool;

    /**
     * Public URL döner (local'de APP_URL/storage/*, S3'de signed URL).
     */
    public function url(string $path): string;
}
