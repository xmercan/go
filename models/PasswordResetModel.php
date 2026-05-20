<?php

namespace GO\Models;

use GO\Core\BaseModel;

class PasswordResetModel extends BaseModel
{
    protected string $table = 'password_resets';

    public function create(string $email): string
    {
        // Eski tokenları sil
        $this->execute("DELETE FROM password_resets WHERE email = ?", [$email]);

        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $now       = date('Y-m-d H:i:s');

        $this->execute(
            "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, ?)",
            [$email, $tokenHash, $expiresAt, $now]
        );

        return $token; // Ham token linke gönderilir
    }

    public function findValid(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);
        return $this->queryOne(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used_at IS NULL LIMIT 1",
            [$tokenHash]
        );
    }

    public function markUsed(string $token): void
    {
        $tokenHash = hash('sha256', $token);
        $this->execute(
            "UPDATE password_resets SET used_at = ? WHERE token = ?",
            [date('Y-m-d H:i:s'), $tokenHash]
        );
    }

    public function cleanup(): void
    {
        $this->execute("DELETE FROM password_resets WHERE expires_at < NOW() OR used_at IS NOT NULL");
    }
}
