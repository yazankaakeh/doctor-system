<?php

namespace Modules\Auth\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\Core\App\Enums\Gender;

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:doctors,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:doctors,phone'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'gender' => ['nullable', 'integer', 'in:'.implode(',', array_column(Gender::cases(), 'value'))],
            'age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'medical_specialty_id' => ['required', 'exists:medical_specialties,id'],
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
            'gender' => __('gender'),
            'age' => __('age'),
            'medical_specialty_id' => __('medical specialty'),
        ];
    }
}
