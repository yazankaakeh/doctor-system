<?php

namespace Modules\MCP\Actions;

use Modules\MCP\Models\ChatFeedback;

class StoreFeedbackAction
{
    public function handle(array $data): ChatFeedback
    {
        // Set reviewer if authenticated
        if (! isset($data['reviewer_type']) && auth()->check()) {
            $data['reviewer_type'] = get_class(auth()->user());
            $data['reviewer_id'] = auth()->id();
        }

        return ChatFeedback::create($data);
    }
}
