<?php

namespace Modules\Patient\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;

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
        $patientId = auth('web')->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('patients', 'email')->ignore($patientId)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('patients', 'phone')->ignore($patientId)],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'gender' => ['nullable', 'string', 'in:'.implode(',', array_column(Gender::cases(), 'value'))],
            'blood_type' => ['nullable', 'string', 'in:'.implode(',', array_column(BloodType::cases(), 'value'))],
            'marital_status' => ['nullable', 'string', 'in:'.implode(',', array_column(MaritalStatus::cases(), 'value'))],
            'work' => ['nullable', 'string', 'max:255'],
            'nationality_id' => ['nullable', 'exists:countries,id'],
            'drug_allergies' => ['nullable', 'string'],
            'disabilities' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'string'],
            'surgical_history' => ['nullable', 'string'],
            'accident_history' => ['nullable', 'string'],
        ];
    }
}
