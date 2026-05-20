<?php

namespace GO\Services;

use GO\Core\Database;

/**
 * Database tabanlı job queue servisi.
 * V1: MySQL jobs tablosu. V2: Redis driver.
 * cPanel cron ile cli/worker.php çalıştırılır.
 */
class QueueService
{
    public const QUEUE_EMAIL        = 'email';
    public const QUEUE_NOTIFICATION = 'notification';
    public const QUEUE_EXPORT       = 'export';
    public const QUEUE_BACKUP       = 'backup';

    public const STATUS_PENDING    = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED  = 'completed';
    public const STATUS_FAILED     = 'failed';

    /**
     * Kuyruğa iş ekle.
     *
     * @param string $queue    Queue adı (email, export, vb.)
     * @param array  $payload  İş verisi
     * @param int    $delay    Saniye cinsinden gecikme (0 = hemen)
     * @return int|null        Job ID
     */
    public function push(string $queue, array $payload, int $delay = 0): ?int
    {
        try {
            $pdo = Database::connection();

            $uuid        = $this->generateUuid();
            $availableAt = date('Y-m-d H:i:s', time() + $delay);
            $now         = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("
                INSERT INTO jobs (uuid, queue, payload, attempts, max_attempts, status, available_at, created_at, updated_at)
                VALUES (?, ?, ?, 0, 3, 'pending', ?, ?, ?)
            ");
            $stmt->execute([
                $uuid,
                $queue,
                json_encode($payload, JSON_UNESCAPED_UNICODE),
                $availableAt,
                $now,
                $now,
            ]);

            return (int)$pdo->lastInsertId();
        } catch (\PDOException) {
            // DB hazır değil — log yaz, sessizce geç
            return null;
        }
    }

    /**
     * Sıradaki işlenecek job'u al ve reserved_at ile kilitle.
     */
    public function pop(string $queue): ?array
    {
        try {
            $pdo = Database::connection();
            $pdo->beginTransaction();

            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("
                SELECT * FROM jobs
                WHERE queue = ?
                  AND status = 'pending'
                  AND available_at <= ?
                ORDER BY available_at ASC
                LIMIT 1
                FOR UPDATE
            ");
            $stmt->execute([$queue, $now]);
            $job = $stmt->fetch();

            if (!$job) {
                $pdo->commit();
                return null;
            }

            // Kilitle
            $update = $pdo->prepare("
                UPDATE jobs SET status = 'processing', reserved_at = ?, attempts = attempts + 1, updated_at = ?
                WHERE id = ?
            ");
            $update->execute([$now, $now, $job['id']]);
            $pdo->commit();

            $job['payload'] = json_decode($job['payload'], true);
            return $job;
        } catch (\PDOException) {
            try { $pdo->rollBack(); } catch (\Throwable) {}
            return null;
        }
    }

    /**
     * Job tamamlandı.
     */
    public function complete(int $jobId): void
    {
        $this->updateStatus($jobId, self::STATUS_COMPLETED);
    }

    /**
     * Job başarısız.
     */
    public function fail(int $jobId, string $reason = ''): void
    {
        try {
            $pdo  = Database::connection();
            $stmt = $pdo->prepare("
                UPDATE jobs
                SET status = CASE WHEN attempts >= max_attempts THEN 'failed' ELSE 'pending' END,
                    reserved_at = NULL,
                    updated_at = ?
                WHERE id = ?
            ");
            $stmt->execute([date('Y-m-d H:i:s'), $jobId]);
        } catch (\PDOException) {}
    }

    /**
     * Bekleyen job sayısı.
     */
    public function pendingCount(string $queue = ''): int
    {
        try {
            $pdo = Database::connection();
            if ($queue) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE queue = ? AND status = 'pending'");
                $stmt->execute([$queue]);
            } else {
                $stmt = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending'");
            }
            return (int)$stmt->fetchColumn();
        } catch (\PDOException) {
            return 0;
        }
    }

    /**
     * Takılı kalmış processing job'ları serbest bırak (worker restart sonrası).
     */
    public function releaseStale(int $minutes = 30): void
    {
        try {
            $pdo  = Database::connection();
            $cutoff = date('Y-m-d H:i:s', time() - $minutes * 60);
            $stmt = $pdo->prepare("
                UPDATE jobs
                SET status = 'pending', reserved_at = NULL
                WHERE status = 'processing' AND reserved_at < ?
            ");
            $stmt->execute([$cutoff]);
        } catch (\PDOException) {}
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function updateStatus(int $id, string $status): void
    {
        try {
            $pdo  = Database::connection();
            $stmt = $pdo->prepare("UPDATE jobs SET status = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$status, date('Y-m-d H:i:s'), $id]);
        } catch (\PDOException) {}
    }

    private function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
