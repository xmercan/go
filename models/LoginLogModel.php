<?php

namespace GO\Models;

use GO\Core\BaseModel;

class LoginLogModel extends BaseModel
{
    protected string $table = 'login_logs';

    public function record(
        string  $actorType,
        ?int    $actorId,
        string  $emailAttempt,
        string  $status
    ): void {
        $this->execute(
            "INSERT INTO login_logs (actor_type, actor_id, email_attempt, ip_address, user_agent, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $actorType,
                $actorId,
                $emailAttempt,
                substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $status,
                date('Y-m-d H:i:s'),
            ]
        );
    }

    public function recent(int $limit = 50): array
    {
        return $this->query(
            "SELECT ll.*, u.full_name as user_name, a.full_name as admin_name
             FROM login_logs ll
             LEFT JOIN users u ON ll.actor_type='user' AND ll.actor_id = u.id
             LEFT JOIN admins a ON ll.actor_type='admin' AND ll.actor_id = a.id
             ORDER BY ll.created_at DESC LIMIT ?",
            [$limit]
        );
    }
}
