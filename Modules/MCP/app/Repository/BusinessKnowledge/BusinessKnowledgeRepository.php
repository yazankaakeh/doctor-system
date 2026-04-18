<?php

namespace Modules\MCP\Repository\BusinessKnowledge;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\MCP\Models\BusinessKnowledge;

class BusinessKnowledgeRepository implements BusinessKnowledgeInterface
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = BusinessKnowledge::query()
            ->with(['knowledgeable'])
            ->highPriority();

        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['subcategory'])) {
            $query->bySubcategory($filters['subcategory']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function store(array $data): BusinessKnowledge
    {
        return BusinessKnowledge::create($data);
    }

    public function update(int $id, array $data): BusinessKnowledge
    {
        $knowledge = $this->find($id);
        $knowledge->update($data);

        return $knowledge->fresh();
    }

    public function find(int $id): BusinessKnowledge
    {
        return BusinessKnowledge::with(['knowledgeable'])
            ->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $knowledge = $this->find($id);
        $knowledge->delete();
    }

    public function search(string $searchTerm, ?string $category = null): Collection
    {
        $query = BusinessKnowledge::active()
            ->search($searchTerm)
            ->highPriority();

        if ($category) {
            $query->byCategory($category);
        }

        return $query->get();
    }

    public function getByCategory(string $category): Collection
    {
        return BusinessKnowledge::active()
            ->byCategory($category)
            ->highPriority()
            ->get();
    }

    public function getActive(): Collection
    {
        return BusinessKnowledge::active()
            ->highPriority()
            ->get();
    }

    public function getPopular(int $limit = 10): Collection
    {
        return BusinessKnowledge::active()
            ->popular()
            ->limit($limit)
            ->get();
    }

    public function incrementUsage(int $id): void
    {
        $knowledge = $this->find($id);
        $knowledge->incrementUsage();
    }

    public function toggleActive(int $id): bool
    {
        $knowledge = $this->find($id);

        return $knowledge->toggleActive();
    }
}
