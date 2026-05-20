<?php

namespace GO\Models;

use GO\Core\BaseModel;

class UserModel extends BaseModel
{
    protected string $table      = 'users';
    protected bool   $softDelete = true;

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );
    }

    public function findByUuidActive(string $uuid): ?array
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE uuid = ? AND deleted_at IS NULL AND status = 'active' LIMIT 1",
            [$uuid]
        );
    }

    public function updateLastLogin(int $id): void
    {
        $this->execute(
            "UPDATE users SET last_login_at = ?, updated_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id]
        );
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $row = $this->queryOne("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1", [$email, $excludeId]);
        } else {
            $row = $this->queryOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
        }
        return $row !== null;
    }

    public function countAll(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM users WHERE deleted_at IS NULL");
        return (int)($row['cnt'] ?? 0);
    }

    public function recentRegistrations(int $days = 7): int
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $row   = $this->queryOne(
            "SELECT COUNT(*) as cnt FROM users WHERE created_at >= ? AND deleted_at IS NULL",
            [$since]
        );
        return (int)($row['cnt'] ?? 0);
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->query(
            "SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }
}
