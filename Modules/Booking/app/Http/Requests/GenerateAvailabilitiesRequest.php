<?php

namespace Modules\Booking\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class GenerateAvailabilitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('doctor')->check();
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->start_date && $this->end_date) {
                $start = Carbon::parse($this->start_date);
                $end = Carbon::parse($this->end_date);

                if ($start->diffInDays($end) > 90) {
                    $validator->errors()->add('end_date', __('booking::recurring.validation.max_days_exceeded'));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_date.required' => __('booking::recurring.validation.start_date_required'),
            'start_date.after_or_equal' => __('booking::recurring.validation.start_date_future'),
            'end_date.required' => __('booking::recurring.validation.end_date_required'),
            'end_date.after' => __('booking::recurring.validation.end_date_after_start'),
        ];
    }

    public function attributes(): array
    {
        return [
            'start_date' => __('booking::recurring.fields.start_date'),
            'end_date' => __('booking::recurring.fields.end_date'),
        ];
    }
}
