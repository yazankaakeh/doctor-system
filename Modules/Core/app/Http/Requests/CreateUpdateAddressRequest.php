<?php

namespace Modules\Core\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUpdateAddressRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isUpdate = $this->filled('address_id');

        return [

            // addressable fields: required only on create
            'addressable_id' => $isUpdate ? 'sometimes|integer' : 'required|integer',
            'addressable_type' => $isUpdate ? 'sometimes|string' : 'required|string',

            // relations
            'country_id' => ['required', Rule::exists('countries', 'id')],
            'city_id' => ['nullable', Rule::exists('cities', 'id')],

            // address fields
            'street' => 'required|string|max:255',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'apartment' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'full_address' => 'nullable|string',

            // optional map coordinates
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            // others
            'is_primary' => 'nullable|boolean',
            'label' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',

            // if updating
            'address_id' => $isUpdate ? ['required', 'exists:addresses,id'] : 'nullable',
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
