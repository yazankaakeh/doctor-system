<?php

namespace Modules\AdminManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Modules\Core\App\Enums\Gender;

/**
 * @property mixed $role
 * @property mixed $password
 * @property mixed $is_active
 * @property mixed $email
 * @property mixed $name
 * @property mixed $id
 */
class UpdateDoctorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:doctors,id'],
            'role' => 'required|integer|exists:roles,id',
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', Rule::unique('doctors')->ignore($this->id), 'string', 'email', 'max:255'],
            // Strong password policy shared with the registration flow.
            // Configured centrally in App\Providers\AppServiceProvider.
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            // Form checkbox posts "on" / nothing; mirror DoctorRequest so the
            // create and update flows accept the same is_active shape.
            'is_active' => ['nullable', 'in:on,off'],
            // age / medicalSpecialtyId are nullable on update because most
            // admin-side update forms don't re-submit them. On create they
            // stay required (see DoctorRequest).
            'age' => ['nullable', 'numeric', 'between:18,100'],
            'medicalSpecialtyId' => ['nullable', 'exists:medical_specialties,id'],
            'gender' => ['required', new Enum(Gender::class)],
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
