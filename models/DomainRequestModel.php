<?php

namespace GO\Models;

use GO\Core\BaseModel;

class DomainRequestModel extends BaseModel
{
    protected string $table = 'domain_requests';

    public function findByUser(int $userId): array
    {
        return $this->query(
            "SELECT dr.*, d.domain_name
             FROM domain_requests dr
             LEFT JOIN domains d ON d.id = dr.domain_id
             WHERE dr.user_id = ?
             ORDER BY dr.created_at DESC",
            [$userId]
        );
    }

    public function kanbanList(): array
    {
        return $this->query(
            "SELECT dr.*, u.full_name as user_name, u.email as user_email, d.domain_name
             FROM domain_requests dr
             LEFT JOIN users u ON u.id = dr.user_id
             LEFT JOIN domains d ON d.id = dr.domain_id
             ORDER BY dr.priority DESC, dr.sort_order ASC, dr.created_at ASC"
        );
    }

    public function updateKanbanStatus(int $id, string $status, int $sortOrder): void
    {
        $this->execute(
            "UPDATE domain_requests SET kanban_status = ?, sort_order = ?, updated_at = NOW() WHERE id = ?",
            [$status, $sortOrder, $id]
        );
    }

    public function countPending(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM domain_requests WHERE kanban_status = 'pending'");
        return (int)($row['cnt'] ?? 0);
    }
}
