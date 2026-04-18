<?php

namespace Modules\Notification\App\Channels;

use Modules\Notification\App\Services\Notifications\SMS;

class SendSMSChannel extends SMS
{
    public function send(mixed $notifiable, $notification): void
    {
        $data = $notification->toSMS($notifiable);
        $this->sendCampaign($data['phoneNumbers'], $data['messageText'], $data['productType'], $notifiable->id);
    }
}
