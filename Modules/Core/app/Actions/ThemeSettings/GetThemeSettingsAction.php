<?php

namespace Modules\Core\App\Actions\ThemeSettings;

use Modules\Core\App\Models\ThemeSetting;

class GetThemeSettingsAction
{
    public function handle(string $scope = 'admin'): ThemeSetting
    {
        return ThemeSetting::query()->firstOrCreate(
            ['scope' => $scope],
            [
                'primary_color' => '#0F0F2D',
                'secondary_color' => '#808390',
                'success_color' => '#28c76f',
                'info_color' => '#00bad1',
                'warning_color' => '#ff9f43',
                'danger_color' => '#ff4c51',
                'font_family' => 'Public Sans',
                'font_size_base' => '0.9375rem',
                'headings_font_weight' => '500',
                'body_bg' => '#f8f7fa',
                'card_bg' => '#ffffff',
                'border_radius' => '0.375rem',
                'is_active' => true,
            ],
        );
    }
}
