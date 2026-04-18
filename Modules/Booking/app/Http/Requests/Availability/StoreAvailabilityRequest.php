<?php

namespace Modules\Booking\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['nullable', 'integer', 'min:10', 'max:120'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['doctor_id'] = auth('doctor')->id();
        $data['slot_duration'] = $data['slot_duration'] ?? config('booking.default_slot_duration', 30);
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }
}
