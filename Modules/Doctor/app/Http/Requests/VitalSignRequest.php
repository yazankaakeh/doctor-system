<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Modules\Core\App\Enums\ActiveEnum;

class VitalSignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->routeIs('doctor.vitalSign.update'); // true/false
        $required = $isUpdate ? 'required' : 'nullable';

        return [
            'id' => [$required, 'integer', 'exists:vital_signs,id'],
            'is_active' => ['required', new Enum(ActiveEnum::class)],
            'name.*' => ['required', 'string', 'max:255'],
            'min_value' => ['nullable', 'numeric', 'min:0'],
            'max_value' => ['nullable', 'numeric', 'min:0', 'gte:min_value'],
            'unit' => ['nullable', 'string', 'max:50'],
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
