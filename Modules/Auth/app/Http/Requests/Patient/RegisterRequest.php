<?php

namespace Modules\Auth\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:patients,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:patients,phone'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'gender' => ['nullable', 'string', 'in:'.implode(',', array_column(Gender::cases(), 'value'))],
            'blood_type' => ['nullable', 'string', 'in:'.implode(',', array_column(BloodType::cases(), 'value'))],
            'marital_status' => ['nullable', 'string', 'in:'.implode(',', array_column(MaritalStatus::cases(), 'value'))],
            'work' => ['nullable', 'string', 'max:255'],
            'nationality_id' => ['nullable', 'exists:countries,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('name'),
            'email' => __('email address'),
            'phone' => __('phone number'),
            'password' => __('password'),
            'age' => __('age'),
            'gender' => __('gender'),
            'blood_type' => __('blood type'),
            'marital_status' => __('marital status'),
            'work' => __('work'),
            'nationality_id' => __('nationality'),
        ];
    }
}
