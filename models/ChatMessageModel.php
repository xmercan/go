<?php

namespace GO\Models;

use GO\Core\BaseModel;

class ChatMessageModel extends BaseModel
{
    protected string $table = 'chat_messages';

    public function getHistory(int $projectId, int $limit = 50): array
    {
        return $this->query(
            "SELECT * FROM chat_messages
             WHERE project_id = ?
             ORDER BY created_at ASC LIMIT ?",
            [$projectId, $limit]
        );
    }

    public function addMessage(
        int    $projectId,
        ?int   $userId,
        string $role,
        string $message,
        string $stateKey = '',
        array  $metadata = []
    ): int {
        return $this->create([
            'project_id' => $projectId,
            'user_id'    => $userId,
            'role'       => $role,
            'message'    => $message,
            'state_key'  => $stateKey,
            'metadata'   => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    public function getLastUserMessage(int $projectId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM chat_messages WHERE project_id = ? AND role = 'user' ORDER BY created_at DESC LIMIT 1",
            [$projectId]
        );
    }

    public function countByProject(int $projectId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) as cnt FROM chat_messages WHERE project_id = ?",
            [$projectId]
        );
        return (int)($row['cnt'] ?? 0);
    }
}
