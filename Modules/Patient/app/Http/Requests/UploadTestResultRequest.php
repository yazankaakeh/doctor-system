<?php

namespace Modules\Patient\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadTestResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'test_result' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'], // 10MB max
        ];
    }

    public function attributes(): array
    {
        return [
            'test_result' => trans('patient::patient.test_result_file'),
        ];
    }

    public function messages(): array
    {
        return [
            'test_result.required' => trans('patient::patient.test_result_file_required'),
            'test_result.mimes' => trans('patient::patient.test_result_file_format'),
            'test_result.max' => trans('patient::patient.test_result_file_size'),
        ];
    }
}
