<?php

namespace Modules\AdminManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed $is_active
 * @property mixed $id
 */
class UpdateStatusAminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // The "user management" screens actually toggle Doctor records
            // (the admin persona is a Doctor with the SUPER_ADMIN role).
            // Validating against `admins,id` made every toggle silently
            // fail — the record was never found in that table.
            'id' => ['required', 'integer', 'exists:doctors,id'],
            'is_active' => ['nullable', 'in:on,off'],
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
