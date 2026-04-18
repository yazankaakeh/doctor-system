<?php

namespace Modules\MCP\Repository\ChatConversation;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\MCP\Models\ChatConversation;

interface ChatConversationInterface
{
    public function index(array $filters = []): LengthAwarePaginator;

    public function findBySessionId(string $sessionId): ?ChatConversation;

    public function store(array $data): ChatConversation;

    public function update(int $id, array $data): ChatConversation;

    public function find(int $id): ChatConversation;

    public function destroy(int $id): void;

    public function findOrCreateBySession(array $data): ChatConversation;

    public function getActiveConversations(): Collection;

    public function getByType(string $type): Collection;

    public function close(int $id): bool;

    public function archive(int $id): bool;
}
