<?php

namespace GO\Models;

use GO\Core\BaseModel;

class SoftwareRequestModel extends BaseModel
{
    protected string $table = 'software_requests';

    public function findByUser(int $userId): array
    {
        return $this->query(
            "SELECT sr.*, ss.product_name
             FROM software_requests sr
             LEFT JOIN software_services ss ON ss.id = sr.software_service_id
             WHERE sr.user_id = ?
             ORDER BY sr.created_at DESC",
            [$userId]
        );
    }

    public function kanbanList(): array
    {
        return $this->query(
            "SELECT sr.*, u.full_name as user_name, u.email as user_email
             FROM software_requests sr
             LEFT JOIN users u ON u.id = sr.user_id
             ORDER BY sr.priority DESC, sr.sort_order ASC, sr.created_at ASC"
        );
    }

    public function updateKanbanStatus(int $id, string $status, int $sortOrder): void
    {
        $this->execute(
            "UPDATE software_requests SET kanban_status = ?, sort_order = ?, updated_at = NOW() WHERE id = ?",
            [$status, $sortOrder, $id]
        );
    }

    public function countPending(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM software_requests WHERE kanban_status = 'pending'");
        return (int)($row['cnt'] ?? 0);
    }
}
