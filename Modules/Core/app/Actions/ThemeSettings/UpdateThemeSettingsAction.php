<?php

namespace Modules\Core\App\Actions\ThemeSettings;

use Modules\Core\App\Http\Requests\ThemeSettingsRequest;
use Modules\Core\App\Models\ThemeSetting;

class UpdateThemeSettingsAction
{
    public function handle(ThemeSettingsRequest $request): ThemeSetting
    {
        $scope = $request->input('scope', 'admin');

        $themeSetting = ThemeSetting::where('scope', $scope)->first();

        if (! $themeSetting) {
            $themeSetting = new ThemeSetting(['scope' => $scope]);
        }

        // Update light mode colors
        $themeSetting->primary_color = $request->input('primary_color');
        $themeSetting->secondary_color = $request->input('secondary_color');
        $themeSetting->success_color = $request->input('success_color');
        $themeSetting->info_color = $request->input('info_color');
        $themeSetting->warning_color = $request->input('warning_color');
        $themeSetting->danger_color = $request->input('danger_color');

        // Update dark mode colors
        $themeSetting->dark_primary_color = $request->input('dark_primary_color');
        $themeSetting->dark_secondary_color = $request->input('dark_secondary_color');
        $themeSetting->dark_success_color = $request->input('dark_success_color');
        $themeSetting->dark_info_color = $request->input('dark_info_color');
        $themeSetting->dark_warning_color = $request->input('dark_warning_color');
        $themeSetting->dark_danger_color = $request->input('dark_danger_color');

        // Update typography
        $themeSetting->font_family = $request->input('font_family');
        $themeSetting->font_import_url = $request->input('font_import_url');
        $themeSetting->font_size_base = $request->input('font_size_base');
        $themeSetting->headings_font_family = $request->input('headings_font_family');
        $themeSetting->headings_font_weight = $request->input('headings_font_weight');

        // Handle custom fonts (array of @font-face declarations)
        if ($request->filled('custom_fonts')) {
            $customFonts = array_filter(
                array_map('trim', explode('---FONT-SEPARATOR---', $request->input('custom_fonts'))),
                fn ($font) => ! empty($font)
            );
            $themeSetting->custom_fonts = ! empty($customFonts) ? $customFonts : null;
        }

        // Update light mode layout
        $themeSetting->body_bg = $request->input('body_bg');
        $themeSetting->card_bg = $request->input('card_bg');
        $themeSetting->border_radius = $request->input('border_radius');

        // Update dark mode layout
        $themeSetting->dark_body_bg = $request->input('dark_body_bg');
        $themeSetting->dark_card_bg = $request->input('dark_card_bg');

        // Update branding
        $themeSetting->site_title = $request->input('site_title');

        // Update advanced settings
        if ($request->filled('custom_css')) {
            $themeSetting->custom_css = $request->input('custom_css');
        }

        if ($request->filled('dark_custom_css')) {
            $themeSetting->dark_custom_css = $request->input('dark_custom_css');
        }

        $themeSetting->save();

        // Handle logo uploads
        if ($request->hasFile('logo')) {
            $themeSetting->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        if ($request->hasFile('logo_dark')) {
            $themeSetting->addMediaFromRequest('logo_dark')->toMediaCollection('logo_dark');
        }

        if ($request->hasFile('favicon')) {
            $themeSetting->addMediaFromRequest('favicon')->toMediaCollection('favicon');
        }

        return $themeSetting;
    }
}
