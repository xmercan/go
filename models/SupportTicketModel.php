<?php

namespace GO\Models;

use GO\Core\BaseModel;

class SupportTicketModel extends BaseModel
{
    protected string $table      = 'support_tickets';
    protected bool   $softDelete = true;

    public function findByUser(int $userId): array
    {
        return $this->query(
            "SELECT t.*, p.name as project_name
             FROM support_tickets t
             LEFT JOIN projects p ON p.id = t.project_id
             WHERE t.user_id = ? AND t.deleted_at IS NULL
             ORDER BY t.updated_at DESC",
            [$userId]
        );
    }

    public function findByUuid(string $uuid): ?array
    {
        return $this->queryOne(
            "SELECT t.*, u.full_name as user_name, u.email as user_email
             FROM support_tickets t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.uuid = ? AND t.deleted_at IS NULL LIMIT 1",
            [$uuid]
        );
    }

    public function getReplies(int $ticketId): array
    {
        return $this->query(
            "SELECT sr.*,
                CASE WHEN sr.author_type='user'  THEN u.full_name
                     WHEN sr.author_type='admin' THEN a.full_name END as author_name
             FROM support_replies sr
             LEFT JOIN users u  ON sr.author_type='user'  AND sr.author_id = u.id
             LEFT JOIN admins a ON sr.author_type='admin' AND sr.author_id = a.id
             WHERE sr.ticket_id = ?
             ORDER BY sr.created_at ASC",
            [$ticketId]
        );
    }

    public function addReply(int $ticketId, string $authorType, int $authorId, string $message): int
    {
        $this->execute(
            "INSERT INTO support_replies (ticket_id, author_type, author_id, message, created_at) VALUES (?, ?, ?, ?, NOW())",
            [$ticketId, $authorType, $authorId, $message]
        );
        $newId = (int)$this->db->lastInsertId();
        $this->execute("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?", [$ticketId]);
        return $newId;
    }

    public function insertReply(int $ticketId, string $authorType, int $authorId, string $message): void
    {
        $this->execute(
            "INSERT INTO support_replies (ticket_id, author_type, author_id, message, created_at) VALUES (?, ?, ?, ?, NOW())",
            [$ticketId, $authorType, $authorId, $message]
        );
    }

    public function countPending(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM support_tickets WHERE status='open' AND deleted_at IS NULL");
        return (int)($row['cnt'] ?? 0);
    }

    public function kanbanList(): array
    {
        return $this->query(
            "SELECT t.*, u.full_name as user_name, u.email as user_email
             FROM support_tickets t
             LEFT JOIN users u ON u.id = t.user_id
             WHERE t.deleted_at IS NULL
             ORDER BY t.priority DESC, t.sort_order ASC, t.created_at ASC"
        );
    }
}
