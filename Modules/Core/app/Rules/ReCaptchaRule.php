<?php

namespace Modules\Core\app\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ReCaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $googleConfig = config('core.recaptcha');

        if (empty($value)) {
            $fail('reCAPTCHA token is required.');

            return;
        }

        try {
            $response = Http::asForm()->post($googleConfig['link'], [
                'secret' => $googleConfig['secret_key'],
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            $responseData = $response->json();

            if (! $responseData['success']) {
                $fail('reCAPTCHA validation failed. Please try again.');

                return;
            }

            // For reCAPTCHA v3, check the score
            if (isset($responseData['score']) && $responseData['score'] < 0.5) {
                $fail('reCAPTCHA score is too low. Please try again.');

                return;
            }

        } catch (ConnectionException $e) {
            $fail('reCAPTCHA validation failed due to connection error. Please try again.');
        } catch (\Exception $e) {
            $fail('reCAPTCHA validation failed. Please try again.');
        }
    }
}
