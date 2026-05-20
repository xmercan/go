<?php

namespace GO\Repositories\Contracts;

/**
 * Proje repository arayüzü.
 * V2'de farklı implementasyon kolayca takılabilir.
 */
interface ProjectRepositoryInterface
{
    public function forUser(int $userId, bool $withDeleted = false): array;
    public function findByUuid(string $uuid): ?array;
    public function findByUuidForUser(string $uuid, int $userId): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function softDelete(int $id): bool;
    public function countForUser(int $userId): int;
    public function recent(int $limit = 5): array;
}
