<?php

namespace GO\Models;

use GO\Core\BaseModel;

class InvoiceModel extends BaseModel
{
    protected string $table      = 'invoices';
    protected bool   $softDelete = true;

    public function findByUser(int $userId): array
    {
        return $this->query(
            "SELECT i.*, p.name as project_name
             FROM invoices i
             LEFT JOIN projects p ON p.id = i.project_id
             WHERE i.user_id = ? AND i.deleted_at IS NULL
             ORDER BY i.created_at DESC",
            [$userId]
        );
    }

    public function findByUuid(string $uuid): ?array
    {
        return $this->queryOne(
            "SELECT i.*, u.full_name as user_name, u.email as user_email, p.name as project_name
             FROM invoices i
             LEFT JOIN users u ON u.id = i.user_id
             LEFT JOIN projects p ON p.id = i.project_id
             WHERE i.uuid = ? AND i.deleted_at IS NULL LIMIT 1",
            [$uuid]
        );
    }

    public function getItemsByInvoice(int $invoiceId): array
    {
        return $this->query(
            "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC",
            [$invoiceId]
        );
    }

    public function getPaymentMethods(int $invoiceId): array
    {
        return $this->query(
            "SELECT * FROM payment_methods WHERE invoice_id = ? ORDER BY type ASC",
            [$invoiceId]
        );
    }

    public function generateNumber(): string
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'invoice_counter' FOR UPDATE");
            $stmt->execute();
            $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
            $counter = (int)($row['setting_value'] ?? 1000) + 1;

            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'invoice_counter'")->execute([$counter]);

            $prefix = $this->getPrefix();
            return $prefix . str_pad((string)$counter, 5, '0', STR_PAD_LEFT);
        } catch (\Throwable) {
            return 'GO-' . date('YmdHis');
        }
    }

    private function getPrefix(): string
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'invoice_prefix'");
            $stmt->execute();
            $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row['setting_value'] ?? 'GO-';
        } catch (\Throwable) {
            return 'GO-';
        }
    }

    public function countAll(): int
    {
        $row = $this->queryOne("SELECT COUNT(*) as cnt FROM invoices WHERE deleted_at IS NULL");
        return (int)($row['cnt'] ?? 0);
    }

    public function totalRevenue(): float
    {
        $row = $this->queryOne("SELECT COALESCE(SUM(total),0) as rev FROM invoices WHERE status='paid' AND deleted_at IS NULL");
        return (float)($row['rev'] ?? 0);
    }

    public function pending(): array
    {
        return $this->query(
            "SELECT i.*, u.full_name as user_name FROM invoices i
             LEFT JOIN users u ON u.id = i.user_id
             WHERE i.status IN ('waiting','payment_pending','payment_reported') AND i.deleted_at IS NULL
             ORDER BY i.created_at DESC"
        );
    }
}
