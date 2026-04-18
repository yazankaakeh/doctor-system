<?php

namespace Modules\CMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HomePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Hero section
            'hero_title' => ['required', 'array'],
            'hero_title.*' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'array'],
            'hero_subtitle.*' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'array'],
            'hero_description.*' => ['nullable', 'string', 'max:1000'],
            'hero_cta_text' => ['nullable', 'array'],
            'hero_cta_text.*' => ['nullable', 'string', 'max:50'],
            'hero_cta_url' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'hero_image_path' => ['nullable', 'string', 'max:255'],

            // Features section
            'features_badge' => ['nullable', 'array'],
            'features_badge.*' => ['nullable', 'string', 'max:100'],
            'features_title' => ['nullable', 'array'],
            'features_title.*' => ['nullable', 'string', 'max:255'],
            'features_description' => ['nullable', 'array'],
            'features_description.*' => ['nullable', 'string', 'max:1000'],

            // CTA section
            'cta_title' => ['nullable', 'array'],
            'cta_title.*' => ['nullable', 'string', 'max:255'],
            'cta_subtitle' => ['nullable', 'array'],
            'cta_subtitle.*' => ['nullable', 'string', 'max:255'],
            'cta_button_text' => ['nullable', 'array'],
            'cta_button_text.*' => ['nullable', 'string', 'max:50'],
            'cta_button_url' => ['nullable', 'string', 'max:255'],

            // Contact section
            'contact_badge' => ['nullable', 'array'],
            'contact_badge.*' => ['nullable', 'string', 'max:100'],
            'contact_title' => ['nullable', 'array'],
            'contact_title.*' => ['nullable', 'string', 'max:255'],
            'contact_description' => ['nullable', 'array'],
            'contact_description.*' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'hero_title.required' => 'Hero title is required.',
            'hero_title.*.required' => 'Hero title is required in all languages.',
            'hero_image.image' => 'Hero image must be an image file.',
            'hero_image.max' => 'Hero image must not exceed 2MB.',
            'contact_email.email' => 'Please enter a valid email address.',
        ];
    }
}
