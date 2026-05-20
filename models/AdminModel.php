<?php

namespace GO\Models;

use GO\Core\BaseModel;

class AdminModel extends BaseModel
{
    protected string $table      = 'admins';
    protected bool   $softDelete = true;

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM admins WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );
    }

    public function updateLastLogin(int $id): void
    {
        $this->execute(
            "UPDATE admins SET last_login_at = ?, updated_at = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id]
        );
    }

    public function isSuperAdmin(int $id): bool
    {
        $row = $this->queryOne("SELECT role FROM admins WHERE id = ?", [$id]);
        return ($row['role'] ?? '') === 'super_admin';
    }
}
