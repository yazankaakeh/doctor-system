<?php

namespace Modules\Core\App\View\Composers;

use Illuminate\View\View;
use Modules\Core\App\Models\ThemeSetting;

class ThemeSettingsComposer
{
    /**
     * Static cache for request-level caching (reset on each request automatically)
     */
    private static array $cache = [];

    /**
     * Bind data to the view.
     * Uses static cache to avoid multiple queries per request.
     */
    public function compose(View $view): void
    {
        // Determine scope based on route or request
        $scope = request()->is('admin/*') || request()->is('doctor/*') ? 'admin' : 'website';

        // Check if already loaded in this request
        if (! isset(self::$cache[$scope])) {
            // Load from database/cache once per request
            $themeSettings = ThemeSetting::getForScope($scope);

            self::$cache[$scope] = [
                'settings' => $themeSettings,
                'css' => $themeSettings?->getFullCustomCss() ?? '',
            ];
        }

        $themeSettings = self::$cache[$scope]['settings'];
        $customThemeCss = self::$cache[$scope]['css'];

        if ($themeSettings) {
            $view->with('themeSettings', $themeSettings);
            $view->with('customThemeCss', $customThemeCss);
        }
    }

    /**
     * Clear the static cache (useful for testing or manual reset)
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
