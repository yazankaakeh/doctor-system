<?php

namespace Modules\Notification\App\Channels;

use Modules\Notification\App\Services\Notifications\DB;

class SendDBChannel extends DB
{
    public function send(mixed $notifiable, $notification): void
    {
        $data = $notification->toDB($notifiable);
        $this->sendDBNotification($data, $notifiable);
    }
}
