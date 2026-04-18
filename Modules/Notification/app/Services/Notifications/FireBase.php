<?php

namespace Modules\Notification\App\Services\Notifications;

use Exception;
use Google_Client;
use Illuminate\Support\Facades\Log;
use Modules\Core\App\Traits\ThirdPartyTrait;

class FireBase
{
    use ThirdPartyTrait;

    public function prepareAndSend($user, $data, $vibrate, $sound, $click_action = null): void
    {
        $tokens = $user->pushTokens;
        if (count($tokens) == 0) {
            Log::debug('no tokens');

            return;
        }

        foreach ($tokens as $token) {
            $this->sendFireBase($token->push_token, $data, $vibrate, $sound, $click_action);
        }
    }

    /**
     * @throws \Google\Exception
     */
    public function sendFireBase($token, $data, $vibrate, $sound, $click_action = null)
    {
        $fields = [
            'message' => [
                'token' => $token,

                'notification' => [
                    'title' => (string) $data['title'],
                    'body' => (string) $data['body'],

                ],
                'data' => [
                    'vibrate' => (string) $vibrate,
                    'sound' => (string) $sound,
                    'click_action' => json_encode($click_action),
                ],
            ],
        ];

        // Get the OAuth token
        $accessToken = $this->getAccessToken();

        // Prepare the request headers
        $headers = [
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json',
        ];

        $projectId = config('services.fireBase.projectId');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        try {
            Log::log('alert', $result);
            $asd = json_decode($result, true);

            return json_decode($result, true);
        } catch (Exception $exception) {
            Log::error("Delete expired token error: {$exception->getMessage()}");
            Log::error($exception->getTraceAsString());
        }
    }

    /**
     * @throws \Google\Exception
     * @throws Exception
     */
    public function getAccessToken()
    {
        // Check for service account file in storage directory first
        $firebaseCredentials = storage_path('firebase-service-account.json');

        // Fallback to config path if not found in storage
        if (! file_exists($firebaseCredentials)) {
            $firebaseCredentials = base_path(config('services.fireBase.firebaseCredentials'));
        }

        if (! file_exists($firebaseCredentials)) {
            throw new Exception('Firebase credentials JSON file not found. Please upload the service account file in the environment settings.');
        }

        $googleCredentials = json_decode(file_get_contents($firebaseCredentials), true);

        $client = new Google_Client;
        $client->setAuthConfig($googleCredentials);
        $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);

        // Fetch the OAuth 2.0 access token
        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            throw new Exception('Failed to generate access token: '.$token['error']);
        }

        Log::info('Generated Access Token: '.json_encode($token));  // Log the token for debugging

        return $token['access_token'];
    }
}
