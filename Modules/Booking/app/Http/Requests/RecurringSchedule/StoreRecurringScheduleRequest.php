<?php

namespace Modules\Booking\Http\Requests\RecurringSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('doctor')->check();
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:5', 'max:240'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date', 'after_or_equal:today'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        $validated['doctor_id'] = auth('doctor')->id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        return $validated;
    }

    public function messages(): array
    {
        return [
            'day_of_week.required' => __('booking::recurring.validation.day_required'),
            'day_of_week.between' => __('booking::recurring.validation.day_invalid'),
            'start_time.required' => __('booking::recurring.validation.start_time_required'),
            'end_time.required' => __('booking::recurring.validation.end_time_required'),
            'end_time.after' => __('booking::recurring.validation.end_time_after_start'),
            'slot_duration.min' => __('booking::recurring.validation.slot_duration_min'),
            'slot_duration.max' => __('booking::recurring.validation.slot_duration_max'),
            'consultation_fee.required' => __('booking::recurring.validation.fee_required'),
            'consultation_fee.min' => __('booking::recurring.validation.fee_min'),
            'effective_from.required' => __('booking::recurring.validation.effective_from_required'),
            'effective_from.after_or_equal' => __('booking::recurring.validation.effective_from_future'),
            'effective_until.after' => __('booking::recurring.validation.effective_until_after'),
        ];
    }

    public function attributes(): array
    {
        return [
            'day_of_week' => __('booking::recurring.fields.day_of_week'),
            'start_time' => __('booking::recurring.fields.start_time'),
            'end_time' => __('booking::recurring.fields.end_time'),
            'slot_duration' => __('booking::recurring.fields.slot_duration'),
            'consultation_fee' => __('booking::recurring.fields.consultation_fee'),
            'effective_from' => __('booking::recurring.fields.effective_from'),
            'effective_until' => __('booking::recurring.fields.effective_until'),
        ];
    }
}
