<?php

namespace App\Services\Interfaces;

use App\Models\Song;

interface ISongService
{
    public function getAll(): array;
    public function getById(int $id): ?Song;
    public function create(array $data, int $userId): int;
    public function update(array $data): bool;
    public function delete(int $id): void;
}
