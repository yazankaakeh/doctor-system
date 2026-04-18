<?php

namespace Modules\Messaging\Livewire;

/**
 * Themed version of GlobalMessagingPanel for dashboard layouts
 * Uses Remix icons and dashboard theme styling
 */
class GlobalMessagingPanelThemed extends GlobalMessagingPanel
{
    public function render()
    {
        return view('messaging::livewire.global-messaging-panel-themed', [
            'channelOptions' => $this->getChannelOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'availableChannels' => $this->getAvailableChannels(),
            'conversationCounts' => $this->conversationCounts,
        ]);
    }
}
