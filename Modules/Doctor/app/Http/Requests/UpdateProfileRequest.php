<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Modules\Core\App\Enums\Gender;

class UpdateProfileRequest extends FormRequest
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
        $doctorId = auth('doctor')->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('doctors', 'email')->ignore($doctorId)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('doctors', 'phone')->ignore($doctorId)],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'age' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:' . implode(',', array_column(Gender::cases(), 'value'))],
            'medical_specialty_id' => ['nullable', 'exists:medical_specialties,id'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => trans('doctor::doctor.profile.name'),
            'email' => trans('doctor::doctor.profile.email'),
            'phone' => trans('doctor::doctor.profile.phone'),
            'password' => trans('doctor::doctor.profile.password'),
            'age' => trans('doctor::doctor.profile.date_of_birth'),
            'gender' => trans('doctor::doctor.profile.gender'),
            'medical_specialty_id' => trans('doctor::doctor.profile.specialty'),
            'bio' => trans('doctor::doctor.profile.bio'),
            'avatar' => trans('doctor::doctor.profile.avatar'),
        ];
    }
}
