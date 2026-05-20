<?php

namespace GO\Services;

/**
 * Feature flag servisi.
 * Admin panelden açılıp kapanabilen özellik bayrakları.
 * Değerler settings tablosundan gelir (CacheService ile).
 */
class FeatureFlagService
{
    private static array $defaults = [
        'ai_enabled'              => false,
        'kanban_enabled'          => true,
        'zip_export_enabled'      => true,
        'domain_module_enabled'   => true,
        'hosting_module_enabled'  => true,
        'software_module_enabled' => true,
        'maintenance_mode'        => false,
        'chat_enabled'            => true,
        'api_enabled'             => false,
        'notifications_enabled'   => true,
        'support_enabled'         => true,
    ];

    private static ?array $loaded = null;
    private CacheService $cache;

    public function __construct()
    {
        $this->cache = new CacheService();
    }

    /**
     * Feature aktif mi?
     */
    public function isEnabled(string $flag): bool
    {
        $flags = $this->loadAll();
        return (bool)($flags[$flag] ?? self::$defaults[$flag] ?? false);
    }

    /**
     * Feature devre dışı mı?
     */
    public function isDisabled(string $flag): bool
    {
        return !$this->isEnabled($flag);
    }

    /**
     * Maintenance mode aktif mi?
     */
    public function isMaintenanceMode(): bool
    {
        return $this->isEnabled('maintenance_mode');
    }

    /**
     * Tüm flag değerlerini al.
     */
    public function all(): array
    {
        return $this->loadAll();
    }

    /**
     * Flag güncelle (admin paneli için).
     * Veritabanı yazımı SettingsController üzerinden yapılır;
     * bu metod sadece cache invalidate eder.
     */
    public function invalidate(): void
    {
        self::$loaded = null;
        $this->cache->delete('feature_flags.all');
    }

    /**
     * Statik helper — dependency injection olmadan kullanım.
     * Kullanım: FeatureFlagService::check('kanban_enabled')
     */
    public static function check(string $flag): bool
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance->isEnabled($flag);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function loadAll(): array
    {
        if (self::$loaded !== null) {
            return self::$loaded;
        }

        // Cache kontrol
        $cached = $this->cache->get('feature_flags.all');
        if ($cached !== null) {
            self::$loaded = $cached;
            return self::$loaded;
        }

        // Veritabanından yükle
        $flags = self::$defaults;

        try {
            $pdo  = \GO\Core\Database::connection();
            $stmt = $pdo->prepare(
                "SELECT setting_key, setting_value FROM settings WHERE group_name = 'feature'"
            );
            $stmt->execute();
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $key = $row['setting_key'];
                $val = $row['setting_value'];
                // "1", "true", "on" → true; diğer → false
                $flags[$key] = in_array(strtolower((string)$val), ['1', 'true', 'on', 'yes'], true);
            }
        } catch (\PDOException) {
            // DB yok veya tablo kurulmadı — varsayılanları kullan
        }

        self::$loaded = $flags;
        $this->cache->set('feature_flags.all', $flags, 300); // 5 dk cache

        return self::$loaded;
    }
}
