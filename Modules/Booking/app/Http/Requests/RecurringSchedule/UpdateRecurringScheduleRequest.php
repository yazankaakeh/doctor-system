<?php

namespace Modules\Booking\Http\Requests\RecurringSchedule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('doctor')->check();
    }

    public function rules(): array
    {
        return [
            'day_of_week' => ['sometimes', 'integer', 'between:0,6'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['sometimes', 'integer', 'min:5', 'max:240'],
            'consultation_fee' => ['sometimes', 'numeric', 'min:0'],
            'effective_from' => ['sometimes', 'date'],
            'effective_until' => ['nullable', 'date', 'after:effective_from'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week.between' => __('booking::recurring.validation.day_invalid'),
            'end_time.after' => __('booking::recurring.validation.end_time_after_start'),
            'slot_duration.min' => __('booking::recurring.validation.slot_duration_min'),
            'slot_duration.max' => __('booking::recurring.validation.slot_duration_max'),
            'consultation_fee.min' => __('booking::recurring.validation.fee_min'),
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
