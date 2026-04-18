<?php

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitOfflinePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        if (!$booking) {
            \Log::warning('Offline payment authorization failed: No booking found');
            return false;
        }

        // Check if user is authenticated
        if (!auth('web')->check()) {
            \Log::warning('Offline payment authorization failed: User not authenticated');
            return false;
        }

        $authorized = $booking->patient_id === auth('web')->id();

        if (!$authorized) {
            \Log::warning('Offline payment authorization failed: Patient mismatch', [
                'booking_patient_id' => $booking->patient_id,
                'auth_user_id' => auth('web')->id(),
            ]);
        }

        return $authorized;
    }

    public function rules(): array
    {
        return [
            'proofs' => ['required', 'array', 'min:1', 'max:5'],
            'proofs.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'proofs.required' => __('payment::payment.proofs_required'),
            'proofs.min' => __('payment::payment.proofs_min'),
            'proofs.max' => __('payment::payment.proofs_max'),
            'proofs.*.file' => __('payment::payment.proof_must_be_file'),
            'proofs.*.mimes' => __('payment::payment.proof_invalid_format'),
            'proofs.*.max' => __('payment::payment.proof_too_large'),
        ];
    }
}
