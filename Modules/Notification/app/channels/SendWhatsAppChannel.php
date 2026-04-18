<?php

namespace Modules\Notification\App\Channels;

use Modules\Notification\App\Services\Notifications\WhatsApp;

class SendWhatsAppChannel extends WhatsApp
{
    public function send(mixed $notifiable, $notification): void
    {
        $data = $notification->toWhatsApp($notifiable);
        $this->sendWhatsApp((int) $data['phone'], $data['templateId'], $data['params'], $notifiable->id);
    }
}
