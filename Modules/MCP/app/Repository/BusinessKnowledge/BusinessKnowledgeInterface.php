<?php

namespace Modules\MCP\Repository\BusinessKnowledge;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\MCP\Models\BusinessKnowledge;

interface BusinessKnowledgeInterface
{
    public function index(array $filters = []): LengthAwarePaginator;

    public function store(array $data): BusinessKnowledge;

    public function update(int $id, array $data): BusinessKnowledge;

    public function find(int $id): BusinessKnowledge;

    public function destroy(int $id): void;

    public function search(string $searchTerm, ?string $category = null): Collection;

    public function getByCategory(string $category): Collection;

    public function getActive(): Collection;

    public function getPopular(int $limit = 10): Collection;

    public function incrementUsage(int $id): void;

    public function toggleActive(int $id): bool;
}
