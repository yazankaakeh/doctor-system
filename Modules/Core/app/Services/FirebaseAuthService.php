<?php

namespace Modules\Core\App\Services;

use Exception;
use Google_Client;
use Illuminate\Support\Facades\Log;

class FirebaseAuthService
{
    /**
     * Initialize Google Client with Firebase credentials
     */
    public function getGoogleClient(): Google_Client
    {
        $client = new Google_Client;

        // Set the path to the service account key file
        $serviceAccountPath = storage_path('firebase-service-account.json');

        if (! file_exists($serviceAccountPath)) {
            throw new Exception('Firebase service account file not found. Please upload it in the environment settings.');
        }

        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->addScope('https://www.googleapis.com/auth/userinfo.email');
        $client->addScope('https://www.googleapis.com/auth/userinfo.profile');

        return $client;
    }

    /**
     * Get Firebase access token for API calls
     */
    public function getAccessToken(): string
    {
        try {
            $client = $this->getGoogleClient();
            $token = $client->fetchAccessTokenWithAssertion();

            if (isset($token['error'])) {
                throw new Exception('Failed to generate access token: '.$token['error']);
            }

            return $token['access_token'];
        } catch (Exception $e) {
            Log::error('Firebase Auth Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Firebase ID token
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $client = $this->getGoogleClient();
            $payload = $client->verifyIdToken($idToken);

            if (! $payload) {
                throw new Exception('Invalid ID token');
            }

            return $payload;
        } catch (Exception $e) {
            Log::error('Firebase ID Token Verification Error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user info from Firebase ID token
     */
    public function getUserInfo(string $idToken): array
    {
        $payload = $this->verifyIdToken($idToken);

        return [
            'uid' => $payload['sub'],
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? null,
            'picture' => $payload['picture'] ?? null,
            'email_verified' => $payload['email_verified'] ?? false,
        ];
    }

    /**
     * Check if Firebase is properly configured
     */
    public function isConfigured(): bool
    {
        try {
            $serviceAccountPath = storage_path('firebase-service-account.json');

            return file_exists($serviceAccountPath);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Firebase configuration for frontend
     */
    public function getFrontendConfig(): array
    {
        return [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
        ];
    }
}
