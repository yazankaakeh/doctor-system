<?php

namespace Modules\Booking\Http\Requests\ScheduleException;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Booking\Models\DoctorScheduleException;

class StoreScheduleExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('doctor')->check();
    }

    public function rules(): array
    {
        return [
            'recurring_schedule_id' => [
                'nullable',
                'integer',
                Rule::exists('doctor_recurring_schedules', 'id')->where(function ($query) {
                    $query->where('doctor_id', auth('doctor')->id());
                }),
            ],
            'exception_date' => ['required', 'date', 'after_or_equal:today'],
            'type' => ['required', Rule::in([DoctorScheduleException::TYPE_SKIP, DoctorScheduleException::TYPE_MODIFIED])],
            'reason' => ['nullable', 'string', 'max:500'],
            'alternate_start_time' => ['required_if:type,modified', 'nullable', 'date_format:H:i'],
            'alternate_end_time' => ['required_if:type,modified', 'nullable', 'date_format:H:i', 'after:alternate_start_time'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        $validated['doctor_id'] = auth('doctor')->id();

        // Clear alternate times for skip type
        if ($validated['type'] === DoctorScheduleException::TYPE_SKIP) {
            $validated['alternate_start_time'] = null;
            $validated['alternate_end_time'] = null;
        }

        return $validated;
    }

    public function messages(): array
    {
        return [
            'exception_date.required' => __('booking::recurring.validation.exception_date_required'),
            'exception_date.after_or_equal' => __('booking::recurring.validation.exception_date_future'),
            'type.required' => __('booking::recurring.validation.exception_type_required'),
            'type.in' => __('booking::recurring.validation.exception_type_invalid'),
            'alternate_start_time.required_if' => __('booking::recurring.validation.alternate_times_required'),
            'alternate_end_time.required_if' => __('booking::recurring.validation.alternate_times_required'),
            'alternate_end_time.after' => __('booking::recurring.validation.alternate_end_after_start'),
        ];
    }

    public function attributes(): array
    {
        return [
            'exception_date' => __('booking::recurring.fields.exception_date'),
            'type' => __('booking::recurring.fields.exception_type'),
            'reason' => __('booking::recurring.fields.reason'),
            'alternate_start_time' => __('booking::recurring.fields.alternate_start_time'),
            'alternate_end_time' => __('booking::recurring.fields.alternate_end_time'),
        ];
    }
}
