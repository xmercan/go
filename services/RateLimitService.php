<?php

namespace GO\Services;

/**
 * Rate limiting servisi.
 * Dosya tabanlı throttle — Redis gerekmez.
 * Her endpoint için IP + user bazlı limit.
 */
class RateLimitService
{
    private string $cachePath;

    // ─── Limit sabitleri ──────────────────────────────────────────────────────
    public const LIMIT_LOGIN          = ['max' => 5,  'window' => 300];   // 5 deneme / 5 dk
    public const LIMIT_FORGOT_PW      = ['max' => 3,  'window' => 900];   // 3 deneme / 15 dk
    public const LIMIT_CHAT_MESSAGE   = ['max' => 30, 'window' => 60];    // 30 mesaj / 1 dk
    public const LIMIT_SUPPORT_CREATE = ['max' => 5,  'window' => 3600];  // 5 ticket / saat
    public const LIMIT_PAYMENT_NOTIFY = ['max' => 3,  'window' => 3600];  // 3 bildirim / saat

    public function __construct()
    {
        $this->cachePath = (defined('CACHE_PATH') ? CACHE_PATH : GO_ROOT . '/storage/cache') . '/ratelimit';
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * İstek yapılabilir mi?
     *
     * @param string   $key    Benzersiz anahtar (örn: 'login.192.168.1.1')
     * @param int      $max    Maksimum istek sayısı
     * @param int      $window Saniye cinsinden pencere
     * @return bool    true = izinli, false = engellendi
     */
    public function attempt(string $key, int $max, int $window): bool
    {
        $data  = $this->getData($key);
        $now   = time();

        // Pencere dışı kayıtları temizle
        $data['hits'] = array_filter($data['hits'] ?? [], fn($t) => $now - $t < $window);

        if (count($data['hits']) >= $max) {
            return false;
        }

        $data['hits'][] = $now;
        $this->saveData($key, $data);
        return true;
    }

    /**
     * Kalan deneme sayısı.
     */
    public function remaining(string $key, int $max, int $window): int
    {
        $data = $this->getData($key);
        $now  = time();
        $hits = array_filter($data['hits'] ?? [], fn($t) => $now - $t < $window);
        return max(0, $max - count($hits));
    }

    /**
     * Ne zaman serbest kalacak (Unix timestamp).
     */
    public function retryAfter(string $key, int $window): int
    {
        $data = $this->getData($key);
        $hits = $data['hits'] ?? [];
        if (empty($hits)) {
            return 0;
        }
        return min($hits) + $window;
    }

    /**
     * Sayacı sıfırla (başarılı girişte).
     */
    public function clear(string $key): void
    {
        $file = $this->filePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // ─── Hazır helper metodlar ────────────────────────────────────────────────

    public function attemptLogin(string $identifier): bool
    {
        $key = 'login.' . $this->safeIp() . '.' . md5($identifier);
        return $this->attempt($key, self::LIMIT_LOGIN['max'], self::LIMIT_LOGIN['window']);
    }

    public function clearLogin(string $identifier): void
    {
        $key = 'login.' . $this->safeIp() . '.' . md5($identifier);
        $this->clear($key);
    }

    public function attemptAdminLogin(string $identifier): bool
    {
        $key = 'admin_login.' . $this->safeIp() . '.' . md5($identifier);
        return $this->attempt($key, self::LIMIT_LOGIN['max'], self::LIMIT_LOGIN['window']);
    }

    public function clearAdminLogin(string $identifier): void
    {
        $key = 'admin_login.' . $this->safeIp() . '.' . md5($identifier);
        $this->clear($key);
    }

    public function attemptForgotPassword(string $email): bool
    {
        $key = 'forgot.' . md5($email);
        return $this->attempt($key, self::LIMIT_FORGOT_PW['max'], self::LIMIT_FORGOT_PW['window']);
    }

    public function attemptChatMessage(int $userId): bool
    {
        $key = 'chat.' . $userId;
        return $this->attempt($key, self::LIMIT_CHAT_MESSAGE['max'], self::LIMIT_CHAT_MESSAGE['window']);
    }

    public function attemptPaymentNotify(int $invoiceId): bool
    {
        $key = 'payment.' . $invoiceId;
        return $this->attempt($key, self::LIMIT_PAYMENT_NOTIFY['max'], self::LIMIT_PAYMENT_NOTIFY['window']);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function getData(string $key): array
    {
        $file = $this->filePath($key);
        if (!file_exists($file)) {
            return ['hits' => []];
        }
        $content = @file_get_contents($file);
        return $content ? (json_decode($content, true) ?? ['hits' => []]) : ['hits' => []];
    }

    private function saveData(string $key, array $data): void
    {
        file_put_contents($this->filePath($key), json_encode($data), LOCK_EX);
    }

    private function filePath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.rl.json';
    }

    private function safeIp(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return preg_replace('/[^0-9a-fA-F:.]/', '', explode(',', $ip)[0]);
    }
}
