<?php

namespace GO\Services;

use GO\Core\Database;

/**
 * In-app bildirim servisi.
 * Aşama 9'da tam implement edilecek; şimdi iskelet.
 */
class NotificationService
{
    /**
     * Kullanıcıya bildirim oluştur.
     */
    public function createForUser(
        int    $userId,
        string $title,
        string $body,
        string $linkType = '',
        mixed  $linkId = null
    ): ?int {
        try {
            $pdo = Database::connection();
            $now = date('Y-m-d H:i:s');
            $uuid = $this->generateUuid();

            $stmt = $pdo->prepare("
                INSERT INTO notifications (uuid, user_id, admin_id, type, title, body, link_type, link_id, is_read, created_at)
                VALUES (?, ?, NULL, 'user', ?, ?, ?, ?, 0, ?)
            ");
            $stmt->execute([$uuid, $userId, $title, $body, $linkType, $linkId, $now]);
            return (int)$pdo->lastInsertId();
        } catch (\PDOException) {
            return null;
        }
    }

    /**
     * Admin'e bildirim oluştur.
     */
    public function createForAdmin(
        int    $adminId,
        string $title,
        string $body,
        string $linkType = ''
    ): ?int {
        try {
            $pdo  = Database::connection();
            $now  = date('Y-m-d H:i:s');
            $uuid = $this->generateUuid();

            $stmt = $pdo->prepare("
                INSERT INTO notifications (uuid, user_id, admin_id, type, title, body, link_type, is_read, created_at)
                VALUES (?, NULL, ?, 'admin', ?, ?, ?, 0, ?)
            ");
            $stmt->execute([$uuid, $adminId, $title, $body, $linkType, $now]);
            return (int)$pdo->lastInsertId();
        } catch (\PDOException) {
            return null;
        }
    }

    /**
     * Okunmamış sayı.
     */
    public function unreadCount(int $userId, string $type = 'user'): int
    {
        try {
            $pdo  = Database::connection();
            $col  = $type === 'admin' ? 'admin_id' : 'user_id';
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE {$col} = ? AND is_read = 0");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException) {
            return 0;
        }
    }

    /**
     * Okundu işaretle.
     */
    public function markRead(int $notificationId): void
    {
        try {
            $pdo  = Database::connection();
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1, read_at = ? WHERE id = ?");
            $stmt->execute([date('Y-m-d H:i:s'), $notificationId]);
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
