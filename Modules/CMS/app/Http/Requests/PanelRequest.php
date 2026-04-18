<?php

namespace Modules\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CMS\Enums\PanelTypeEnum;
use Modules\Core\App\Enums\LanguageEnum;

class PanelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'page_id' => ['required', 'integer', 'exists:cms_pages,id'],
            'type' => ['required', 'string', Rule::in(PanelTypeEnum::values())],
            'slug' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
            'panel_image' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:2048'],
        ];

        // Add translatable title fields
        foreach (LanguageEnum::values() as $lang) {
            $rules["title.{$lang}"] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [];

        foreach (LanguageEnum::values() as $lang) {
            $messages["title.{$lang}.required"] = 'Title in '.strtoupper($lang).' is required';
        }

        return $messages;
    }
}
