<?php

namespace GO\Core;

use PDO;

/**
 * Tüm model'lerin temel sınıfı.
 * PDO wrapper, CRUD, soft delete. SQL yalnızca burada.
 */
abstract class BaseModel
{
    protected PDO    $db;
    protected string $table  = '';
    protected string $pk     = 'id';
    protected bool   $softDelete = false;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // ─── Temel CRUD ───────────────────────────────────────────────────────────

    /**
     * Tüm kayıtları al.
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        $where = $this->softDelete ? 'WHERE deleted_at IS NULL' : '';
        $sql   = "SELECT * FROM `{$this->table}` {$where} ORDER BY {$orderBy}";
        return $this->query($sql);
    }

    /**
     * ID ile bul.
     */
    public function find(int $id): ?array
    {
        $where = $this->softDelete ? 'AND deleted_at IS NULL' : '';
        $sql   = "SELECT * FROM `{$this->table}` WHERE `{$this->pk}` = ? {$where} LIMIT 1";
        return $this->queryOne($sql, [$id]);
    }

    /**
     * UUID ile bul.
     */
    public function findByUuid(string $uuid): ?array
    {
        $where = $this->softDelete ? 'AND deleted_at IS NULL' : '';
        $sql   = "SELECT * FROM `{$this->table}` WHERE `uuid` = ? {$where} LIMIT 1";
        return $this->queryOne($sql, [$uuid]);
    }

    /**
     * Koşulla bul.
     */
    public function findWhere(array $conditions, string $orderBy = 'id DESC'): array
    {
        [$where, $bindings] = $this->buildWhere($conditions);
        $softWhere = $this->softDelete ? 'AND deleted_at IS NULL' : '';
        $sql = "SELECT * FROM `{$this->table}` WHERE {$where} {$softWhere} ORDER BY {$orderBy}";
        return $this->query($sql, $bindings);
    }

    /**
     * Tek kayıt bul (koşulla).
     */
    public function findOneWhere(array $conditions): ?array
    {
        [$where, $bindings] = $this->buildWhere($conditions);
        $softWhere = $this->softDelete ? 'AND deleted_at IS NULL' : '';
        $sql = "SELECT * FROM `{$this->table}` WHERE {$where} {$softWhere} LIMIT 1";
        return $this->queryOne($sql, $bindings);
    }

    /**
     * Yeni kayıt oluştur.
     * @return int Yeni kayıt ID
     */
    public function create(array $data): int
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');

        // UUID otomatik ekle (tablo varsa)
        if (!isset($data['uuid']) && $this->hasColumn('uuid')) {
            $data['uuid'] = $this->generateUuid();
        }

        $columns  = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $this->execute($sql, array_values($data));

        return (int)$this->db->lastInsertId();
    }

    /**
     * Kayıt güncelle.
     */
    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $set = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $sql = "UPDATE `{$this->table}` SET {$set} WHERE `{$this->pk}` = ?";

        $result = $this->execute($sql, [...array_values($data), $id]);
        return $result->rowCount() > 0;
    }

    /**
     * Soft delete (deleted_at doldur) veya hard delete.
     */
    public function delete(int $id): bool
    {
        if ($this->softDelete) {
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }

        $sql    = "DELETE FROM `{$this->table}` WHERE `{$this->pk}` = ?";
        $result = $this->execute($sql, [$id]);
        return $result->rowCount() > 0;
    }

    /**
     * Soft-deleted kaydı geri yükle.
     */
    public function restore(int $id): bool
    {
        $sql = "UPDATE `{$this->table}` SET `deleted_at` = NULL, `updated_at` = ? WHERE `{$this->pk}` = ?";
        $result = $this->execute($sql, [date('Y-m-d H:i:s'), $id]);
        return $result->rowCount() > 0;
    }

    /**
     * Sayım.
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $where    = $this->softDelete ? 'WHERE deleted_at IS NULL' : '';
            $bindings = [];
        } else {
            [$where, $bindings] = $this->buildWhere($conditions);
            $softWhere = $this->softDelete ? 'AND deleted_at IS NULL' : '';
            $where = "WHERE {$where} {$softWhere}";
        }

        $sql = "SELECT COUNT(*) as cnt FROM `{$this->table}` {$where}";
        $row = $this->queryOne($sql, $bindings);
        return (int)($row['cnt'] ?? 0);
    }

    // ─── PDO yardımcıları ─────────────────────────────────────────────────────

    /**
     * SELECT → array of rows
     */
    protected function query(string $sql, array $bindings = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * SELECT → tek satır
     */
    protected function queryOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * INSERT / UPDATE / DELETE
     */
    protected function execute(string $sql, array $bindings = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    // ─── Yardımcı metodlar ────────────────────────────────────────────────────

    private function buildWhere(array $conditions): array
    {
        $parts    = [];
        $bindings = [];

        foreach ($conditions as $column => $value) {
            if ($value === null) {
                $parts[] = "`{$column}` IS NULL";
            } else {
                $parts[]    = "`{$column}` = ?";
                $bindings[] = $value;
            }
        }

        return [implode(' AND ', $parts), $bindings];
    }

    private function hasColumn(string $column): bool
    {
        // Basit kontrol — tablo şeması cache'de tutulabilir (V2)
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `{$this->table}` LIKE ?");
            $stmt->execute([$column]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException) {
            return false;
        }
    }

    /**
     * UUID v4 üretici (composer'sız).
     */
    public static function generateUuid(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versiyon 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Transaction wrapper.
     */
    public function transaction(callable $callback): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $callback($this);
            $this->db->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
