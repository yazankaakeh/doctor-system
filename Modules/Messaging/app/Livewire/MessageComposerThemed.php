<?php

namespace Modules\Messaging\Livewire;

/**
 * Themed version of MessageComposer for dashboard layouts.
 * Uses Remix icons and dashboard theme styling.
 */
class MessageComposerThemed extends MessageComposer
{
    public function render()
    {
        return view('messaging::livewire.message-composer-themed');
    }
}
