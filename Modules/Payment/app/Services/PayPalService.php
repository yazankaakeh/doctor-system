<?php

namespace Modules\Payment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->clientId = config('payment.paypal.client_id');
        $this->clientSecret = config('payment.paypal.client_secret');
        $this->baseUrl = config('payment.paypal.sandbox', true)
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public function createOrder(float $amount, string $currency, string $description): array
    {
        $this->authenticate();

        $response = Http::withToken($this->accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'description' => $description,
                    ],
                ],
                'application_context' => [
                    'return_url' => route('payment.paypal.success'),
                    'cancel_url' => route('payment.paypal.cancel'),
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (!$response->successful()) {
            Log::error('PayPal create order failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception(__('payment::payment.paypal_order_failed'));
        }

        $data = $response->json();
        $approvalUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        return [
            'id' => $data['id'],
            'status' => $data['status'],
            'approval_url' => $approvalUrl,
        ];
    }

    public function captureOrder(string $orderId): array
    {
        $this->authenticate();

        $response = Http::withToken($this->accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if (!$response->successful()) {
            Log::error('PayPal capture order failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception(__('payment::payment.paypal_capture_failed'));
        }

        $data = $response->json();

        $transactionId = null;
        if (isset($data['purchase_units'][0]['payments']['captures'][0]['id'])) {
            $transactionId = $data['purchase_units'][0]['payments']['captures'][0]['id'];
        }

        return [
            'status' => $data['status'],
            'transaction_id' => $transactionId,
            'payer' => $data['payer'] ?? null,
            'raw' => $data,
        ];
    }

    public function getOrder(string $orderId): array
    {
        $this->authenticate();

        $response = Http::withToken($this->accessToken)
            ->get("{$this->baseUrl}/v2/checkout/orders/{$orderId}");

        if (!$response->successful()) {
            throw new \Exception(__('payment::payment.paypal_order_not_found'));
        }

        return $response->json();
    }

    private function authenticate(): void
    {
        if ($this->accessToken) {
            return;
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::error('PayPal authentication failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception(__('payment::payment.paypal_auth_failed'));
        }

        $this->accessToken = $response->json()['access_token'];
    }
}
