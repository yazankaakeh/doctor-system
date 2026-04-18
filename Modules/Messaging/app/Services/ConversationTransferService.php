<?php

namespace Modules\Messaging\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Messaging\Enums\ConversationStatusEnum;
use Modules\Messaging\Enums\MessageDirectionEnum;
use Modules\Messaging\Enums\MessageTypeEnum;
use Modules\Messaging\Events\ConversationAssigned;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;

class ConversationTransferService
{
    /**
     * Transfer a conversation to another agent.
     */
    public function transfer(
        Conversation $conversation,
        User $targetAgent,
        ?User $sourceAgent = null,
        ?string $note = null
    ): bool {
        $sourceAgent = $sourceAgent ?? $conversation->assignedTo;
        $previousAgentId = $conversation->assigned_to;

        try {
            // Update conversation assignment
            $conversation->update([
                'assigned_to' => $targetAgent->id,
                'assigned_at' => now(),
                'status' => ConversationStatusEnum::ACTIVE,
                'metadata' => array_merge($conversation->metadata ?? [], [
                    'transfers' => array_merge(
                        $conversation->metadata['transfers'] ?? [],
                        [[
                            'from' => $previousAgentId,
                            'to' => $targetAgent->id,
                            'note' => $note,
                            'at' => now()->toIso8601String(),
                        ]]
                    ),
                ]),
            ]);

            // Add internal note about the transfer
            if ($note) {
                $this->addTransferNote($conversation, $sourceAgent, $targetAgent, $note);
            }

            Log::channel('messaging')->info('Conversation transferred', [
                'conversation_id' => $conversation->id,
                'from_agent' => $previousAgentId,
                'to_agent' => $targetAgent->id,
            ]);

            // Broadcast the assignment
            event(new ConversationAssigned($conversation, $targetAgent, $previousAgentId));

            return true;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Failed to transfer conversation', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Transfer to a department/team (first available agent).
     */
    public function transferToDepartment(Conversation $conversation, string $department, ?string $note = null): bool
    {
        // Find available agent in department
        $agent = User::query()
            ->whereHas('roles', function ($query) use ($department) {
                $query->where('name', $department);
            })
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $agent) {
            Log::channel('messaging')->warning('No available agent in department', [
                'conversation_id' => $conversation->id,
                'department' => $department,
            ]);

            return false;
        }

        return $this->transfer($conversation, $agent, null, $note ?? "Transferred to {$department} department");
    }

    /**
     * Escalate conversation to supervisor.
     */
    public function escalate(Conversation $conversation, ?string $reason = null): bool
    {
        // Find a supervisor/admin
        $supervisor = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'supervisor', 'manager']);
            })
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $supervisor) {
            Log::channel('messaging')->warning('No supervisor available for escalation', [
                'conversation_id' => $conversation->id,
            ]);

            return false;
        }

        // Mark as escalated in metadata
        $conversation->update([
            'metadata' => array_merge($conversation->metadata ?? [], [
                'escalated' => true,
                'escalated_at' => now()->toIso8601String(),
                'escalation_reason' => $reason,
            ]),
        ]);

        return $this->transfer($conversation, $supervisor, null, "ESCALATED: " . ($reason ?? 'No reason provided'));
    }

    /**
     * Return conversation to queue (unassign).
     */
    public function returnToQueue(Conversation $conversation, ?string $reason = null): bool
    {
        $previousAgentId = $conversation->assigned_to;

        try {
            $conversation->update([
                'assigned_to' => null,
                'assigned_at' => null,
                'status' => ConversationStatusEnum::PENDING,
                'metadata' => array_merge($conversation->metadata ?? [], [
                    'returned_to_queue' => [
                        'by' => $previousAgentId,
                        'reason' => $reason,
                        'at' => now()->toIso8601String(),
                    ],
                ]),
            ]);

            if ($reason) {
                $this->addInternalNote($conversation, $previousAgentId, "Returned to queue: {$reason}");
            }

            Log::channel('messaging')->info('Conversation returned to queue', [
                'conversation_id' => $conversation->id,
                'by_agent' => $previousAgentId,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::channel('messaging')->error('Failed to return conversation to queue', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Add internal note about transfer.
     */
    protected function addTransferNote(
        Conversation $conversation,
        ?User $fromAgent,
        User $toAgent,
        string $note
    ): void {
        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => MessageDirectionEnum::OUTBOUND,
            'message_type' => MessageTypeEnum::INTERNAL_NOTE,
            'content' => sprintf(
                '[Transfer] From %s to %s: %s',
                $fromAgent?->name ?? 'Unassigned',
                $toAgent->name,
                $note
            ),
            'sender_id' => $fromAgent?->id,
            'metadata' => [
                'is_internal' => true,
                'transfer_note' => true,
            ],
        ]);
    }

    /**
     * Add internal note.
     */
    protected function addInternalNote(Conversation $conversation, ?int $agentId, string $note): void
    {
        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => MessageDirectionEnum::OUTBOUND,
            'message_type' => MessageTypeEnum::INTERNAL_NOTE,
            'content' => $note,
            'sender_id' => $agentId,
            'metadata' => [
                'is_internal' => true,
            ],
        ]);
    }

    /**
     * Get transfer history for a conversation.
     */
    public function getTransferHistory(Conversation $conversation): array
    {
        return $conversation->metadata['transfers'] ?? [];
    }

    /**
     * Check if conversation can be transferred.
     */
    public function canTransfer(Conversation $conversation): bool
    {
        // Can't transfer closed conversations
        if ($conversation->status === ConversationStatusEnum::CLOSED) {
            return false;
        }

        return true;
    }
}
