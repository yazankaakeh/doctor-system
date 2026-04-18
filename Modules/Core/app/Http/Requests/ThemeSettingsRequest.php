<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemeSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add your authorization logic here
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'scope' => 'required|in:admin,website',

            // Light Mode Colors
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'success_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'info_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'warning_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'danger_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            // Dark Mode Colors
            'dark_primary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_secondary_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_success_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_info_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_warning_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_danger_color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],

            // Typography
            'font_family' => 'required|string|max:255',
            'font_import_url' => 'nullable|url|max:500',
            'custom_fonts' => 'nullable|string|max:65535',
            'font_size_base' => 'required|string|max:20',
            'headings_font_family' => 'nullable|string|max:255',
            'headings_font_weight' => 'nullable|string|max:20',

            // Layout
            'body_bg' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'card_bg' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_body_bg' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'dark_card_bg' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'border_radius' => 'required|string|max:20',

            // Branding
            'site_title' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'logo_dark' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'favicon' => 'nullable|mimes:ico,png|max:512',

            // Advanced
            'custom_css' => 'nullable|string|max:65535',
            'dark_custom_css' => 'nullable|string|max:65535',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'primary_color' => trans('core::core.theme_settings.primary_color'),
            'secondary_color' => trans('core::core.theme_settings.secondary_color'),
            'success_color' => trans('core::core.theme_settings.success_color'),
            'info_color' => trans('core::core.theme_settings.info_color'),
            'warning_color' => trans('core::core.theme_settings.warning_color'),
            'danger_color' => trans('core::core.theme_settings.danger_color'),
            'font_family' => trans('core::core.theme_settings.font_family'),
            'font_size_base' => trans('core::core.theme_settings.font_size_base'),
            'body_bg' => trans('core::core.theme_settings.body_bg'),
            'card_bg' => trans('core::core.theme_settings.card_bg'),
            'border_radius' => trans('core::core.theme_settings.border_radius'),
            'site_title' => trans('core::core.theme_settings.site_title'),
            'logo' => trans('core::core.theme_settings.logo'),
            'custom_css' => trans('core::core.theme_settings.custom_css'),
        ];
    }
}
