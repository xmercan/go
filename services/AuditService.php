<?php

namespace GO\Services;

use GO\Core\Database;

/**
 * Immutable audit trail servisi.
 * Kritik admin işlemleri bu servis üzerinden loglanır.
 * audit_trail tablosuna yalnızca INSERT yapılır — UPDATE/DELETE yasak.
 */
class AuditService
{
    // ─── Action sabitleri ─────────────────────────────────────────────────────
    public const ACTION_PAYMENT_APPROVED       = 'payment_approved';
    public const ACTION_PAYMENT_REJECTED       = 'payment_rejected';
    public const ACTION_DOMAIN_TRANSFER_ISSUED = 'domain_transfer_issued';
    public const ACTION_USER_SUSPENDED         = 'user_suspended';
    public const ACTION_USER_DELETED           = 'user_deleted';
    public const ACTION_ADMIN_CREATED          = 'admin_created';
    public const ACTION_ADMIN_ROLE_CHANGED     = 'admin_role_changed';
    public const ACTION_FEATURE_FLAG_CHANGED   = 'feature_flag_changed';
    public const ACTION_SMTP_CHANGED           = 'smtp_settings_changed';
    public const ACTION_INVOICE_CREATED        = 'invoice_created';
    public const ACTION_PROJECT_DELETED        = 'project_deleted';

    /**
     * Audit kaydı oluştur.
     *
     * @param string     $action      Sabit (yukarıdaki ACTION_*)
     * @param string     $entityType  'invoice', 'user', 'project', vb.
     * @param int        $entityId    Etkilenen kayıt ID
     * @param array|null $oldValues   Önceki değerler (şifre gibi hassas alanlar hariç)
     * @param array|null $newValues   Yeni değerler
     * @param string     $actorType   'admin' | 'system'
     * @param int|null   $actorId     Admin ID
     */
    public function log(
        string  $action,
        string  $entityType,
        int     $entityId,
        ?array  $oldValues = null,
        ?array  $newValues = null,
        string  $actorType = 'admin',
        ?int    $actorId = null,
        ?string $entityUuid = null
    ): void {
        try {
            $pdo = Database::connection();
            $now = date('Y-m-d H:i:s');

            // Hassas alanları temizle
            $oldValues = $this->stripSensitive($oldValues);
            $newValues = $this->stripSensitive($newValues);

            $stmt = $pdo->prepare("
                INSERT INTO audit_trail
                    (uuid, actor_type, actor_id, action, entity_type, entity_id, entity_uuid,
                     old_values, new_values, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $this->generateUuid(),
                $actorType,
                $actorId ?? auth_admin_id(),
                $action,
                $entityType,
                $entityId,
                $entityUuid,
                $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $now,
            ]);
        } catch (\PDOException $e) {
            // Audit başarısız olsa bile uygulama durmamalı; log dosyasına yaz
            $this->fallbackLog($action, $entityType, $entityId, $e->getMessage());
        }
    }

    /**
     * Statik helper — dependency injection olmadan.
     */
    public static function write(
        string $action,
        string $entityType,
        int    $entityId,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        (new self())->log($action, $entityType, $entityId, $oldValues, $newValues);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    /**
     * Hassas alanları loga yazma.
     */
    private function stripSensitive(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $sensitive = ['password', 'password_hash', 'api_key', 'smtp_pass', 'auth_code', 'token'];

        foreach ($sensitive as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '[GİZLİ]';
            }
        }

        return $data;
    }

    private function fallbackLog(string $action, string $entity, int $id, string $error): void
    {
        $logPath = defined('LOG_PATH') ? LOG_PATH : GO_ROOT . '/storage/logs';
        $msg     = '[' . date('Y-m-d H:i:s') . '] AUDIT_FAIL action=' . $action
                 . ' entity=' . $entity . ' id=' . $id . ' error=' . $error . PHP_EOL;
        @file_put_contents($logPath . '/audit-errors.log', $msg, FILE_APPEND | LOCK_EX);
    }

    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
