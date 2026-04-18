<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Core\App\Enums\ActiveEnum;

class FinalDiagnosisRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->routeIs('doctor.finalDiagnosis.update'); // true/false
        $required = $isUpdate ? 'required' : 'nullable';

        return [
            'id' => [$required, 'integer', 'exists:final_diagnoses,id'],
            'is_active' => ['required', new Enum(ActiveEnum::class)],           // must be true/false
            'name.*' => ['required', 'string', 'max:255'],
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
