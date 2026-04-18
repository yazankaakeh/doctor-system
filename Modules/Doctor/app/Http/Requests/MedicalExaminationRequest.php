<?php

namespace Modules\Doctor\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Doctor\Enums\MedicalExaminationStatusEnum;

/**
 * @property numeric $id
 * @property string $reason_of_visiting
 * @property string $clinical_examination
 * @property string $impression
 * @property string $request_for_action
 * @property string $note
 */
class MedicalExaminationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                Rule::exists('medical_examinations', 'id')
                    ->where(fn ($query) => $query->whereNot('status', MedicalExaminationStatusEnum::ARCHIVED),
                    ),
            ],
            'reason_of_visiting' => ['required', 'string', 'max:255'],
            'medical_story' => ['required', 'string', 'max:255'],
            'clinical_examination' => ['required', 'string'],
            'impression' => ['required', 'string', 'max:255'],
            'request_for_action' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],

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
