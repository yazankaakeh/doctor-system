<?php

namespace Modules\AdminManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed|null $role_management
 * @property mixed $name
 * @property mixed $permissions
 */
class PermissionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array'],
            'name' => ['required', 'unique:roles,name,'.$this?->role_management],
            'permissions.*' => ['required', 'exists:permissions,name', 'string'],
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
