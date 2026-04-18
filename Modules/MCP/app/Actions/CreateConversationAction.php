<?php

namespace Modules\MCP\Actions;

use Illuminate\Support\Str;
use Modules\MCP\Models\ChatConversation;
use Modules\MCP\Repository\ChatConversation\ChatConversationInterface;

class CreateConversationAction
{
    public function __construct(
        protected ChatConversationInterface $conversationRepository
    ) {}

    public function handle(array $data): ChatConversation
    {
        // Generate unique session ID if not provided
        if (! isset($data['session_id'])) {
            $data['session_id'] = Str::uuid()->toString();
        }

        // Set default status
        $data['status'] = $data['status'] ?? 'active';

        // Add IP and user agent if available
        $data['ip_address'] = $data['ip_address'] ?? request()->ip();
        $data['user_agent'] = $data['user_agent'] ?? request()->userAgent();

        // Set conversationable if user is authenticated
        if (! isset($data['conversationable_type']) && auth()->check()) {
            $data['conversationable_type'] = get_class(auth()->user());
            $data['conversationable_id'] = auth()->id();
        }

        return $this->conversationRepository->findOrCreateBySession($data);
    }
}
