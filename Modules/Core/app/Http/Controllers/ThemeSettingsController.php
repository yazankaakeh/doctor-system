<?php

namespace Modules\Core\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\App\Actions\ThemeSettings\GetThemeSettingsAction;
use Modules\Core\App\Actions\ThemeSettings\UpdateThemeSettingsAction;
use Modules\Core\App\Http\Requests\ThemeSettingsRequest;
use Modules\Core\App\Models\ThemeSetting;

class ThemeSettingsController extends Controller
{
    /**
     * Show the theme settings page
     */
    public function index(GetThemeSettingsAction $action): View
    {
        $adminSettings = $action->handle('admin');

        $websiteSettings = $action->handle('website');

        return view('core::theme-settings.index', compact('adminSettings', 'websiteSettings'));
    }

    /**
     * Reset theme settings to defaults
     */
    public function reset(Request $request): RedirectResponse
    {
        $scope = $request->input('scope', 'admin');

        $themeSetting = ThemeSetting::query()->where('scope', $scope)->first();

        if ($themeSetting) {
            // Determine defaults based on scope
            $defaults = $scope === 'admin' ? [
                // Light mode colors
                'primary_color' => '#696cff',
                'secondary_color' => '#8592a3',
                'success_color' => '#71dd37',
                'info_color' => '#03c3ec',
                'warning_color' => '#ffab00',
                'danger_color' => '#ff3e1d',
                // Dark mode colors
                'dark_primary_color' => '#696cff',
                'dark_secondary_color' => '#8592a3',
                'dark_success_color' => '#71dd37',
                'dark_info_color' => '#03c3ec',
                'dark_warning_color' => '#ffab00',
                'dark_danger_color' => '#ff3e1d',
                // Typography
                'font_family' => 'Public Sans',
                'font_import_url' => 'https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap',
                'custom_fonts' => null,
                'font_size_base' => '0.9375rem',
                'headings_font_family' => 'Public Sans',
                'headings_font_weight' => '500',
                // Layout
                'body_bg' => '#f5f5f9',
                'card_bg' => '#ffffff',
                'dark_body_bg' => '#232333',
                'dark_card_bg' => '#2b2c40',
                'border_radius' => '0.375rem',
                'site_title' => 'Base Project Admin',
                'custom_css' => null,
                'dark_custom_css' => null,
            ] : [
                // Website defaults
                'primary_color' => '#0F0F2D',
                'secondary_color' => '#6c757d',
                'success_color' => '#28a745',
                'info_color' => '#17a2b8',
                'warning_color' => '#ffc107',
                'danger_color' => '#dc3545',
                'dark_primary_color' => '#696cff',
                'dark_secondary_color' => '#8592a3',
                'dark_success_color' => '#71dd37',
                'dark_info_color' => '#03c3ec',
                'dark_warning_color' => '#ffab00',
                'dark_danger_color' => '#ff3e1d',
                'font_family' => 'Poppins',
                'font_import_url' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
                'custom_fonts' => null,
                'font_size_base' => '1rem',
                'headings_font_family' => 'Poppins',
                'headings_font_weight' => '600',
                'body_bg' => '#ffffff',
                'card_bg' => '#f8f9fa',
                'dark_body_bg' => '#1a1a2e',
                'dark_card_bg' => '#16213e',
                'border_radius' => '0.5rem',
                'site_title' => 'Base Project',
                'custom_css' => null,
                'dark_custom_css' => null,
            ];

            // Reset to defaults
            $themeSetting->update($defaults);

            return redirect()
                ->route('theme.settings.index')
                ->with('success', trans('core::core.theme_settings.reset_successfully'));
        }

        return redirect()
            ->route('theme.settings.index')
            ->with('error', trans('core::core.theme_settings.reset_failed'));
    }

    /**
     * Update theme settings
     */
    public function update(
        ThemeSettingsRequest $request,
        UpdateThemeSettingsAction $action,
    ): RedirectResponse {
        try {
            $action->handle($request);

            return redirect()
                ->route('theme.settings.index')
                ->with('success', trans('core::core.theme_settings.updated_successfully'));
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', trans('core::core.theme_settings.update_failed').': '.$e->getMessage())
                ->withInput();
        }
    }
}
