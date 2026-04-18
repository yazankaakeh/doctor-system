<?php

namespace Modules\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CMS\Enums\PanelItemTypeEnum;
use Modules\Core\App\Enums\LanguageEnum;

class PanelItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'panel_id' => ['required', 'integer', 'exists:cms_panels,id'],
            'type' => ['required', 'string', Rule::in(PanelItemTypeEnum::values())],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'data' => ['nullable', 'array'],
            'item_image' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:2048'],
        ];

        // Add translatable title and content fields
        foreach (LanguageEnum::values() as $lang) {
            $rules["title.{$lang}"] = ['nullable', 'string', 'max:500'];
            $rules["content.{$lang}"] = ['nullable', 'string'];
        }

        // Add data fields based on item type
        if ($this->filled('type')) {
            $type = PanelItemTypeEnum::tryFrom($this->input('type'));
            if ($type) {
                $rules = array_merge($rules, $this->getDataRulesForType($type));
            }
        }

        return $rules;
    }

    protected function getDataRulesForType(PanelItemTypeEnum $type): array
    {
        return match ($type) {
            PanelItemTypeEnum::FEATURE_CARD => [
                'data.icon' => ['nullable', 'string', 'max:100'],
            ],
            PanelItemTypeEnum::TEAM_MEMBER => [
                'data.social_links' => ['nullable', 'array'],
                'data.social_links.*' => ['url'],
            ],
            PanelItemTypeEnum::REVIEW => [
                'data.rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            ],
            PanelItemTypeEnum::STAT => [
                'data.icon' => ['nullable', 'string', 'max:100'],
                'data.number' => ['nullable', 'string', 'max:50'],
                'data.suffix' => ['nullable', 'string', 'max:10'],
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        $messages = [];

        foreach (LanguageEnum::values() as $lang) {
            $messages["title.{$lang}.max"] = 'Title in '.strtoupper($lang).' must not exceed 500 characters';
        }

        return $messages;
    }
}
