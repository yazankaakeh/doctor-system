<?php

namespace Modules\MCP\Repository\ChatConversation;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\MCP\Models\ChatConversation;

class ChatConversationRepository implements ChatConversationInterface
{
    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = ChatConversation::query()
            ->with(['messages', 'conversationable'])
            ->latest();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['search'])) {
            $query->where('session_id', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findBySessionId(string $sessionId): ?ChatConversation
    {
        return ChatConversation::where('session_id', $sessionId)
            ->with(['messages', 'conversationable'])
            ->first();
    }

    public function store(array $data): ChatConversation
    {
        return ChatConversation::create($data);
    }

    public function update(int $id, array $data): ChatConversation
    {
        $conversation = $this->find($id);
        $conversation->update($data);

        return $conversation->fresh();
    }

    public function find(int $id): ChatConversation
    {
        return ChatConversation::with(['messages', 'conversationable'])
            ->findOrFail($id);
    }

    public function destroy(int $id): void
    {
        $conversation = $this->find($id);
        $conversation->delete();
    }

    public function findOrCreateBySession(array $data): ChatConversation
    {
        return ChatConversation::firstOrCreate(
            ['session_id' => $data['session_id']],
            $data
        );
    }

    public function getActiveConversations(): Collection
    {
        return ChatConversation::active()
            ->with(['latestMessage', 'conversationable'])
            ->latest('last_message_at')
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return ChatConversation::byType($type)
            ->with(['messages', 'conversationable'])
            ->latest()
            ->get();
    }

    public function close(int $id): bool
    {
        $conversation = $this->find($id);

        return $conversation->close();
    }

    public function archive(int $id): bool
    {
        $conversation = $this->find($id);

        return $conversation->archive();
    }
}
