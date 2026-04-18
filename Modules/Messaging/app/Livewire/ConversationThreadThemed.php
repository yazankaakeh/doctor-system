<?php

namespace Modules\Messaging\Livewire;

/**
 * Themed version of ConversationThread for dashboard layouts.
 * Uses Remix icons and dashboard theme styling.
 */
class ConversationThreadThemed extends ConversationThread
{
    public function render()
    {
        return view('messaging::livewire.conversation-thread-themed');
    }
}
