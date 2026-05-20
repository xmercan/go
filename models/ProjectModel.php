<?php

namespace GO\Models;

use GO\Core\BaseModel;

class ProjectModel extends BaseModel
{
    protected string $table      = 'projects';
    protected bool   $softDelete = true;

    public function findByUser(int $userId): array
    {
        return $this->query(
            "SELECT p.*, s.name as sector_name, s.icon as sector_icon
             FROM projects p
             LEFT JOIN sectors s ON s.id = p.sector_id
             WHERE p.user_id = ? AND p.deleted_at IS NULL
             ORDER BY p.updated_at DESC",
            [$userId]
        );
    }

    public function findActiveByUser(int $userId, int $projectId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM projects WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1",
            [$projectId, $userId]
        );
    }

    public function findByUuid(string $uuid): ?array
    {
        return $this->queryOne(
            "SELECT * FROM projects WHERE uuid = ? AND deleted_at IS NULL LIMIT 1",
            [$uuid]
        );
    }

    public function countByUser(int $userId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) as cnt FROM projects WHERE user_id = ? AND deleted_at IS NULL",
            [$userId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public function countAll(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM projects WHERE deleted_at IS NULL");
        return (int)($row['cnt'] ?? 0);
    }

    public function updateStatus(int $id, string $processStatus): void
    {
        $this->update($id, ['process_status' => $processStatus]);
    }

    public function updateAiContext(int $id, array $context): void
    {
        $this->update($id, [
            'ai_context'       => json_encode($context, JSON_UNESCAPED_UNICODE),
            'last_ai_analysis' => date('Y-m-d H:i:s'),
        ]);
    }

    public function setChatCompleted(int $id): void
    {
        $this->update($id, [
            'chat_completed_at' => date('Y-m-d H:i:s'),
            'process_status'    => 'queued',
        ]);
    }

    public function adminList(int $page = 1, int $perPage = 25, string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = $status ? "AND p.process_status = '{$status}'" : '';

        return $this->query(
            "SELECT p.*, u.full_name as user_name, u.email as user_email, s.name as sector_name
             FROM projects p
             LEFT JOIN users u ON u.id = p.user_id
             LEFT JOIN sectors s ON s.id = p.sector_id
             WHERE p.deleted_at IS NULL {$where}
             ORDER BY p.updated_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    public function countByStatus(): array
    {
        $rows = $this->query(
            "SELECT process_status, COUNT(*) as cnt FROM projects WHERE deleted_at IS NULL GROUP BY process_status"
        );
        $result = [];
        foreach ($rows as $row) {
            $result[$row['process_status']] = (int)$row['cnt'];
        }
        return $result;
    }
}
