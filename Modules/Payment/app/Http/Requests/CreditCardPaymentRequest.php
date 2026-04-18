<?php

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreditCardPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->check();
    }

    /**
     * Normalize input before validation so the doctor/patient can paste
     * spaces in the card number or slashes in expiry without it failing.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'card_number' => preg_replace('/\D+/', '', (string) $this->input('card_number')),
            'cvv'         => preg_replace('/\D+/', '', (string) $this->input('cvv')),
            'expiry_month'=> str_pad((string) $this->input('expiry_month'), 2, '0', STR_PAD_LEFT),
            'expiry_year' => (string) $this->input('expiry_year'),
        ]);
    }

    public function rules(): array
    {
        return [
            'cardholder_name' => ['required', 'string', 'min:2', 'max:60'],
            'card_number'     => ['required', 'digits_between:13,19', 'string', 'luhn'],
            'expiry_month'    => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])$/'],
            'expiry_year'     => ['required', 'digits:4'],
            'cvv'             => ['required', 'digits_between:3,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_number.luhn'  => trans('payment::payment.credit_card.invalid_card'),
            'expiry_month.regex' => trans('payment::payment.credit_card.invalid_expiry'),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $month = (int) $this->input('expiry_month');
            $year  = (int) $this->input('expiry_year');

            if ($month && $year) {
                // last day of that month
                $expiryEnd = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();
                if ($expiryEnd->isPast()) {
                    $validator->errors()->add(
                        'expiry_year',
                        trans('payment::payment.credit_card.card_expired'),
                    );
                }
            }
        });
    }
}
