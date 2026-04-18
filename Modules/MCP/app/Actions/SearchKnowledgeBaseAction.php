<?php

namespace Modules\MCP\Actions;

use Illuminate\Support\Facades\Cache;
use Modules\MCP\Repository\BusinessKnowledge\BusinessKnowledgeInterface;

class SearchKnowledgeBaseAction
{
    public function __construct(
        protected BusinessKnowledgeInterface $knowledgeRepository
    ) {}

    public function handle(string $searchTerm, ?string $category = null): array
    {
        $cacheKey = 'knowledge_search:'.md5($searchTerm.$category);
        $cacheTtl = config('mcp.knowledge_base.cache_ttl', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($searchTerm, $category) {
            $results = $this->knowledgeRepository->search($searchTerm, $category);

            $searchLimit = config('mcp.knowledge_base.search_limit', 5);

            return $results->take($searchLimit)->map(function ($item) {
                // Increment usage count
                $this->knowledgeRepository->incrementUsage($item->id);

                return [
                    'id' => $item->id,
                    'category' => $item->category,
                    'question' => $item->question,
                    'answer' => $item->answer,
                    'priority' => $item->priority,
                ];
            })->toArray();
        });
    }
}
