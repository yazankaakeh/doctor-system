<?php

namespace Modules\Notification\App\Services\Notifications;

use GuzzleHttp\Client;
use Modules\Core\App\Traits\ThirdPartyTrait;

class WhatsApp
{
    use ThirdPartyTrait;

    public function sendWhatsApp(int $phone, $templateId, array $data, $customerID): void
    {
        $client = new Client;

        $whatsAppConfig = config('services.whatsApp');
        $url = $whatsAppConfig['url'];
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'apikey' => $whatsAppConfig['token'],
            'Cache-Control' => 'no-cache',
        ];
        // https://api.gupshup.io/wa/api/v1/template/msg
        // https://api.gupshup.io/sm/api/v1/template/msg/bba80eb6-f2dd-4690-88fe-8841a7940fbc/status
        $templateParams = [
            'id' => $templateId,
            'params' => $data,
        ];
        $params = [
            'channel' => 'whatsapp',
            'source' => $whatsAppConfig['source'],
            'destination' => $phone,
            'src.name' => $whatsAppConfig['source_name'],
            'template' => json_encode($templateParams),
        ];

        $response = $client->post($url, [
            'headers' => $headers,
            'form_params' => $params,
        ]);
        $status = json_decode($response->getBody()->getContents(), true);
        $this->saveLog($customerID, $templateId, $status['messageId']);
    }
}
