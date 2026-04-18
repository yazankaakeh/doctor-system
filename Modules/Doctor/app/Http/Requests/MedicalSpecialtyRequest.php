<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Core\App\Enums\ActiveEnum;

class MedicalSpecialtyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->routeIs('doctor.medicalSpecialty.update'); // true/false
        $required = $isUpdate ? 'required' : 'nullable';

        return [
            'id' => [$required, 'integer', 'exists:medical_specialties,id'],
            'name.*' => ['required', 'string', 'max:255'], // array of names
            'is_active' => ['required', new Enum(ActiveEnum::class)],           // must be true/false
            'code' => ['required', 'string', 'min:1', 'max:255'], // optional image

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
