<?php

namespace Modules\Messaging\Livewire;

/**
 * Themed version of GlobalMessagingIcon for dashboard layouts
 * Uses Remix icons and tm-header-icon-btn styling
 */
class GlobalMessagingIconThemed extends GlobalMessagingIcon
{
    public function render()
    {
        return view('messaging::livewire.global-messaging-icon-themed');
    }
}
