<?php

namespace Modules\Notification\App\Channels;

use Google\Exception;
use Illuminate\Support\Facades\Log;
use Modules\Notification\App\Services\Notifications\FireBase;

class SendPushNotificationChannel extends FireBase
{
    /**
     * Send the given notification.
     *
     * @throws Exception
     */
    public function send(mixed $notifiable, $notification): void
    {
        $data = $notification->toFireBase();
        Log::debug('sending push notification to user id: '.$notifiable->getKey());
        $this->prepareAndSend(
            $notifiable,
            $data['data'],
            $data['vibrate'],
            $data['sound'],
            $data['click_action'],
        );
    }
}
