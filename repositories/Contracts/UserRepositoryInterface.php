<?php

namespace GO\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?array;
    public function findById(int $id): ?array;
    public function findByUuid(string $uuid): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function updateLastLogin(int $id): void;
    public function all(int $page = 1, int $perPage = 20): array;
    public function count(): int;
}
