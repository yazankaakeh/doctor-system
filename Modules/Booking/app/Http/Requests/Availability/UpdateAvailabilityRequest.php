<?php

namespace Modules\Booking\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $availability = $this->route('availability');

        return $availability && $availability->doctor_id === auth('doctor')->id();
    }

    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'date', 'after_or_equal:today'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['nullable', 'integer', 'min:10', 'max:120'],
            'consultation_fee' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
