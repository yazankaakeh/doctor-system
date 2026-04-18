<?php

namespace Modules\Core\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\app\Rules\ReCaptchaRule;

class ContactUsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fullName' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'message' => ['required', 'string', 'min:3', 'max:255'],
            'recaptcha' => ['required', new ReCaptchaRule],

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
