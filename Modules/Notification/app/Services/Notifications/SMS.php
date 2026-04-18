<?php

namespace Modules\Notification\App\Services\Notifications;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Traits\ThirdPartyTrait;

class SMS
{
    use ThirdPartyTrait;

    protected Client $client;

    private string $apiUrl = 'https://smscloud.alsardfiber.com/api/v1/campaign/infinite';

    private string $apiToken;

    public function __construct()
    {
        $this->client = new Client;
        $this->apiToken = config('app.sms_cloud_api_token');
    }

    public function sendCampaign($phoneNumber, $messageText, $productType, $customerId)
    {
        $payload = [
            'phone_number' => $phoneNumber,
            'message_text' => $messageText,
            'product_type' => $productType,
            'ignore_schedule' => true,
            'ignore_stop_list' => true,
            'timezone' => 'Europe/Istanbul',
        ];

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $response_json = json_decode($response->getBody()->getContents(), true);
            $this->saveLog($customerId, $response_json['message_id']);

            return $response_json;
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage = $e->getResponse()->getBody()->getContents();
            }

            // Log the exception with the customer transaction ID
            Log::error('SMS Campaign API request failed', [
                'customer_id' => $customerId,
                'error' => $errorMessage,
            ]);

            return $errorMessage;
        }
    }
}
