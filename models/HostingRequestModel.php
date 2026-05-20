<?php

namespace GO\Models;

use GO\Core\BaseModel;

class HostingRequestModel extends BaseModel
{
    protected string $table = 'hosting_requests';

    public function findByUser(int $userId): array
    {
        return $this->query(
            "SELECT hr.*, hs.package_name
             FROM hosting_requests hr
             LEFT JOIN hosting_services hs ON hs.id = hr.hosting_service_id
             WHERE hr.user_id = ?
             ORDER BY hr.created_at DESC",
            [$userId]
        );
    }

    public function kanbanList(): array
    {
        return $this->query(
            "SELECT hr.*, u.full_name as user_name, u.email as user_email
             FROM hosting_requests hr
             LEFT JOIN users u ON u.id = hr.user_id
             ORDER BY hr.priority DESC, hr.sort_order ASC, hr.created_at ASC"
        );
    }

    public function updateKanbanStatus(int $id, string $status, int $sortOrder): void
    {
        $this->execute(
            "UPDATE hosting_requests SET kanban_status = ?, sort_order = ?, updated_at = NOW() WHERE id = ?",
            [$status, $sortOrder, $id]
        );
    }

    public function countPending(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM hosting_requests WHERE kanban_status = 'pending'");
        return (int)($row['cnt'] ?? 0);
    }
}
