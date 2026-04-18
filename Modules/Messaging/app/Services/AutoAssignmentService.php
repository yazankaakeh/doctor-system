<?php

namespace Modules\Messaging\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ChannelTypeEnum;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Events\ConversationAssigned;
use Modules\Messaging\Models\Conversation;

class AutoAssignmentService
{
    /**
     * Assignment strategies.
     */
    public const STRATEGY_ROUND_ROBIN = 'round_robin';
    public const STRATEGY_LEAST_BUSY = 'least_busy';
    public const STRATEGY_RANDOM = 'random';
    public const STRATEGY_MANUAL = 'manual';

    protected string $strategy;

    public function __construct()
    {
        $this->strategy = config('messaging.assignment.strategy', self::STRATEGY_ROUND_ROBIN);
    }

    /**
     * Auto-assign a conversation to an available agent.
     */
    public function assign(Conversation $conversation, bool $force = false): ?User
    {
        // Don't re-assign if already assigned (unless forced)
        if ($conversation->assigned_to && ! $force) {
            return $conversation->assignedTo;
        }

        // Skip if manual assignment is configured
        if ($this->strategy === self::STRATEGY_MANUAL) {
            return null;
        }

        $availableAgents = $this->getAvailableAgents($conversation->channel->type);

        if ($availableAgents->isEmpty()) {
            Log::channel('messaging')->warning('No available agents for assignment', [
                'conversation_id' => $conversation->id,
                'channel' => $conversation->channel->type->value,
            ]);
            return null;
        }

        $agent = match ($this->strategy) {
            self::STRATEGY_ROUND_ROBIN => $this->roundRobinSelect($availableAgents, $conversation->channel->type),
            self::STRATEGY_LEAST_BUSY => $this->leastBusySelect($availableAgents),
            self::STRATEGY_RANDOM => $availableAgents->random(),
            default => $availableAgents->first(),
        };

        if ($agent) {
            $this->assignToAgent($conversation, $agent);
        }

        return $agent;
    }

    /**
     * Get available agents for a channel type.
     */
    protected function getAvailableAgents(ChannelTypeEnum $channelType)
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'messaging_agent', 'support']);
            })
            ->where('is_active', true)
            ->where(function ($query) {
                // Check if user is online (active in last 5 minutes)
                $query->where('last_activity_at', '>=', now()->subMinutes(5))
                    ->orWhereNull('last_activity_at'); // Include users without activity tracking
            })
            ->get();
    }

    /**
     * Round-robin selection.
     */
    protected function roundRobinSelect($agents, ChannelTypeEnum $channelType): ?User
    {
        $cacheKey = "assignment:round_robin:{$channelType->value}";
        $lastAssignedId = Cache::get($cacheKey, 0);

        // Find the next agent after the last assigned
        $nextAgent = $agents->filter(fn ($agent) => $agent->id > $lastAssignedId)->first();

        if (! $nextAgent) {
            // Wrap around to the beginning
            $nextAgent = $agents->first();
        }

        if ($nextAgent) {
            Cache::put($cacheKey, $nextAgent->id, 3600);
        }

        return $nextAgent;
    }

    /**
     * Least busy selection (agent with fewest active conversations).
     */
    protected function leastBusySelect($agents): ?User
    {
        $agentLoads = [];

        foreach ($agents as $agent) {
            $activeCount = Conversation::where('assigned_to', $agent->id)
                ->where('status', ConversationStatusEnum::ACTIVE)
                ->count();

            $agentLoads[$agent->id] = $activeCount;
        }

        // Sort by load (ascending) and get first
        asort($agentLoads);
        $leastBusyId = array_key_first($agentLoads);

        return $agents->firstWhere('id', $leastBusyId);
    }

    /**
     * Assign conversation to a specific agent.
     */
    public function assignToAgent(Conversation $conversation, User $agent): void
    {
        $previousAgent = $conversation->assigned_to;

        $conversation->update([
            'assigned_to' => $agent->id,
            'assigned_at' => now(),
            'status' => ConversationStatusEnum::ACTIVE,
        ]);

        Log::channel('messaging')->info('Conversation assigned', [
            'conversation_id' => $conversation->id,
            'agent_id' => $agent->id,
            'previous_agent' => $previousAgent,
            'strategy' => $this->strategy,
        ]);

        event(new ConversationAssigned($conversation, $agent, $previousAgent));
    }

    /**
     * Unassign a conversation.
     */
    public function unassign(Conversation $conversation): void
    {
        $previousAgent = $conversation->assigned_to;

        $conversation->update([
            'assigned_to' => null,
            'assigned_at' => null,
        ]);

        Log::channel('messaging')->info('Conversation unassigned', [
            'conversation_id' => $conversation->id,
            'previous_agent' => $previousAgent,
        ]);
    }

    /**
     * Transfer conversation to another agent.
     */
    public function transfer(Conversation $conversation, User $newAgent, ?string $reason = null): void
    {
        $previousAgent = $conversation->assigned_to;

        $conversation->update([
            'assigned_to' => $newAgent->id,
            'assigned_at' => now(),
            'metadata' => array_merge($conversation->metadata ?? [], [
                'last_transfer' => [
                    'from' => $previousAgent,
                    'to' => $newAgent->id,
                    'reason' => $reason,
                    'at' => now()->toIso8601String(),
                ],
            ]),
        ]);

        Log::channel('messaging')->info('Conversation transferred', [
            'conversation_id' => $conversation->id,
            'from_agent' => $previousAgent,
            'to_agent' => $newAgent->id,
            'reason' => $reason,
        ]);

        event(new ConversationAssigned($conversation, $newAgent, $previousAgent));
    }

    /**
     * Get agent workload statistics.
     */
    public function getAgentWorkload(User $agent): array
    {
        return [
            'active_conversations' => Conversation::where('assigned_to', $agent->id)
                ->where('status', ConversationStatusEnum::ACTIVE)
                ->count(),
            'pending_conversations' => Conversation::where('assigned_to', $agent->id)
                ->where('status', ConversationStatusEnum::PENDING)
                ->count(),
            'total_today' => Conversation::where('assigned_to', $agent->id)
                ->whereDate('assigned_at', today())
                ->count(),
            'messages_today' => \Modules\Messaging\Models\Message::whereHas('conversation', function ($q) use ($agent) {
                $q->where('assigned_to', $agent->id);
            })->whereDate('created_at', today())->count(),
        ];
    }
}
