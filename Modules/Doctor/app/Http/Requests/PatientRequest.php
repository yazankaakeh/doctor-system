<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Enums\BloodType;
use Modules\Doctor\Enums\MaritalStatus;
use Modules\Doctor\Models\Patient;

class PatientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->routeIs('doctor.patients.update');
        $required = $isUpdate ? 'required' : 'nullable';

        return [
            'id' => [$required, 'integer', 'exists:patients,id'],
            'nationality_id' => ['required', 'integer', 'exists:countries,id'],
            'clinics_id.*' => ['required', 'integer', 'exists:clinics,id'],
            'name' => ['required', 'string', 'max:255', 'min:5'],
            'phone' => [
                'required',
                'string',
                'max:13',
                'min:9',
                Rule::unique(Patient::class, 'phone')
                    ->ignore($this->id),
            ],
            'age' => ['required', 'date'],
            'gender' => ['required', new Enum(Gender::class)],
            'marital_status' => ['required', new Enum(MaritalStatus::class)],
            'children' => ['nullable', 'string', 'max:255'],
            'work' => ['required', 'string'],
            'blood_type' => ['required', new Enum(BloodType::class)],
            'drug_allergies' => ['nullable', 'string', 'max:255', 'min:3'],
            'disabilities' => ['nullable', 'string', 'max:255', 'min:3'],
            'medical_history' => ['nullable', 'string', 'max:255', 'min:3'],
            'surgical_history' => ['nullable', 'string', 'max:255', 'min:3'],
            'accident_history' => ['nullable', 'string', 'max:255', 'min:3'],
            'password' => ['nullable', 'min:8', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'email:rfc,dns',
                Rule::unique(Patient::class, 'email')->ignore($this->id),
            ],
            'is_active' => ['required', new Enum(ActiveEnum::class)],
            'img' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
