<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

class ThemeSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Static request-level cache to prevent multiple DB queries per request
     */
    private static array $requestCache = [];

    protected $fillable = [
        'scope',
        'primary_color',
        'secondary_color',
        'success_color',
        'info_color',
        'warning_color',
        'danger_color',
        'dark_primary_color',
        'dark_secondary_color',
        'dark_success_color',
        'dark_info_color',
        'dark_warning_color',
        'dark_danger_color',
        'font_family',
        'font_import_url',
        'custom_fonts',
        'font_size_base',
        'headings_font_family',
        'headings_font_weight',
        'body_bg',
        'card_bg',
        'dark_body_bg',
        'dark_card_bg',
        'border_radius',
        'logo_path',
        'logo_dark_path',
        'favicon_path',
        'site_title',
        'custom_css_variables',
        'custom_css',
        'dark_custom_css',
        'is_active',
    ];

    protected $casts = [
        'custom_css_variables' => 'array',
        'custom_fonts' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Marker value to detect cached null (distinguishes from cache miss)
     */
    private const NULL_CACHE_MARKER = '__NULL_THEME_SETTING__';

    /**
     * Get theme settings for a specific scope
     * Uses request-level caching to avoid multiple DB queries per request
     */
    public static function getForScope(string $scope = 'admin'): ?self
    {
        // Check request-level cache first (avoids repeated cache/DB hits within same request)
        if (array_key_exists($scope, self::$requestCache)) {
            return self::$requestCache[$scope];
        }

        // Use Laravel cache for persistence across requests
        $cacheKey = "theme_settings_{$scope}";
        $cached = Cache::get($cacheKey);

        if ($cached === self::NULL_CACHE_MARKER) {
            // Null was intentionally cached
            $result = null;
        } elseif ($cached instanceof self) {
            // Valid cached model
            $result = $cached;
        } else {
            // Cache miss - query the database
            $result = self::where('scope', $scope)
                ->where('is_active', true)
                ->first();

            // Cache the result (use marker for null to distinguish from cache miss)
            Cache::put($cacheKey, $result ?? self::NULL_CACHE_MARKER, 3600);
        }

        // Store in request cache
        self::$requestCache[$scope] = $result;

        return $result;
    }

    /**
     * Boot method to clear cache on save
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($themeSetting) {
            self::clearCache($themeSetting->scope);
        });

        static::deleted(function ($themeSetting) {
            self::clearCache($themeSetting->scope);
        });
    }

    /**
     * Clear theme settings cache (both Laravel cache and request cache)
     */
    public static function clearCache(?string $scope = null): void
    {
        // Clear request cache
        if ($scope) {
            unset(self::$requestCache[$scope]);
            Cache::forget("theme_settings_{$scope}");
        } else {
            self::$requestCache = [];
            Cache::forget('theme_settings_admin');
            Cache::forget('theme_settings_website');
        }
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('logo')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/svg+xml',
                    'image/webp',
                ], true);
            })->singleFile();

        $this
            ->addMediaCollection('logo_dark')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/svg+xml',
                    'image/webp',
                ], true);
            })->singleFile();

        $this
            ->addMediaCollection('favicon')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/x-icon',
                    'image/vnd.microsoft.icon',
                    'image/png',
                ], true);
            })->singleFile();
    }

    /**
     * Get full custom CSS including font imports and variables
     */
    public function getFullCustomCss(): string
    {
        $css = '';

        // Add Google Font import if provided
        if ($this->font_import_url) {
            $css .= '@import url(\''.$this->font_import_url.'\');'.PHP_EOL.PHP_EOL;
        }

        // Add custom font @font-face declarations
        if ($this->custom_fonts && is_array($this->custom_fonts)) {
            foreach ($this->custom_fonts as $fontDeclaration) {
                if (! empty($fontDeclaration)) {
                    $css .= $fontDeclaration.PHP_EOL.PHP_EOL;
                }
            }
        }

        $css .= $this->getCssVariables();

        if ($this->custom_css) {
            $css .= PHP_EOL.PHP_EOL.'/* Light Mode Custom CSS */'.PHP_EOL.$this->custom_css;
        }

        if ($this->dark_custom_css) {
            $css .= PHP_EOL.PHP_EOL.'/* Dark Mode Custom CSS */'.PHP_EOL.$this->dark_custom_css;
        }

        return $css;
    }

    /**
     * Convert hex color to RGB values
     */
    private function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }

    /**
     * Get CSS variables as a string for both light and dark modes
     */
    public function getCssVariables(): string
    {
        // Light mode variables with both hex and RGB values
        $lightVariables = [
            // Primary colors (hex and RGB)
            '--bs-primary' => $this->primary_color,
            '--bs-primary-rgb' => $this->hexToRgb($this->primary_color),
            '--bs-secondary' => $this->secondary_color,
            '--bs-secondary-rgb' => $this->hexToRgb($this->secondary_color),
            '--bs-success' => $this->success_color,
            '--bs-success-rgb' => $this->hexToRgb($this->success_color),
            '--bs-info' => $this->info_color,
            '--bs-info-rgb' => $this->hexToRgb($this->info_color),
            '--bs-warning' => $this->warning_color,
            '--bs-warning-rgb' => $this->hexToRgb($this->warning_color),
            '--bs-danger' => $this->danger_color,
            '--bs-danger-rgb' => $this->hexToRgb($this->danger_color),

            // Background and layout colors
            '--bs-body-bg' => $this->body_bg,
            '--bs-body-bg-rgb' => $this->hexToRgb($this->body_bg),
            '--bs-card-bg' => $this->card_bg,
            '--bs-card-bg-rgb' => $this->hexToRgb($this->card_bg),
            '--bs-border-radius' => $this->border_radius,

            // Typography
            '--bs-body-font-family' => '"'.$this->font_family.'", sans-serif',
            '--bs-body-font-size' => $this->font_size_base,
        ];

        if ($this->headings_font_family) {
            $lightVariables['--bs-heading-font-family'] = '"'.$this->headings_font_family.'", sans-serif';
        }

        if ($this->headings_font_weight) {
            $lightVariables['--bs-heading-font-weight'] = $this->headings_font_weight;
        }

        // Merge custom CSS variables for light mode
        if ($this->custom_css_variables) {
            $lightVariables = array_merge($lightVariables, $this->custom_css_variables);
        }

        // Dark mode variables with both hex and RGB values
        $darkVariables = [
            '--bs-primary' => $this->dark_primary_color,
            '--bs-primary-rgb' => $this->hexToRgb($this->dark_primary_color),
            '--bs-secondary' => $this->dark_secondary_color,
            '--bs-secondary-rgb' => $this->hexToRgb($this->dark_secondary_color),
            '--bs-success' => $this->dark_success_color,
            '--bs-success-rgb' => $this->hexToRgb($this->dark_success_color),
            '--bs-info' => $this->dark_info_color,
            '--bs-info-rgb' => $this->hexToRgb($this->dark_info_color),
            '--bs-warning' => $this->dark_warning_color,
            '--bs-warning-rgb' => $this->hexToRgb($this->dark_warning_color),
            '--bs-danger' => $this->dark_danger_color,
            '--bs-danger-rgb' => $this->hexToRgb($this->dark_danger_color),
            '--bs-body-bg' => $this->dark_body_bg,
            '--bs-body-bg-rgb' => $this->hexToRgb($this->dark_body_bg),
            '--bs-card-bg' => $this->dark_card_bg,
            '--bs-card-bg-rgb' => $this->hexToRgb($this->dark_card_bg),
        ];

        // Generate CSS for light mode
        $css = ':root, [data-bs-theme="light"] {'.PHP_EOL;
        foreach ($lightVariables as $key => $value) {
            $css .= "  {$key}: {$value};".PHP_EOL;
        }
        $css .= '}'.PHP_EOL.PHP_EOL;

        // Apply font family to body and headings
        $css .= 'body {'.PHP_EOL;
        $css .= '  font-family: var(--bs-body-font-family) !important;'.PHP_EOL;
        $css .= '}'.PHP_EOL.PHP_EOL;

        if ($this->headings_font_family) {
            $css .= 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {'.PHP_EOL;
            $css .= '  font-family: var(--bs-heading-font-family) !important;'.PHP_EOL;
            $css .= '  font-weight: var(--bs-heading-font-weight) !important;'.PHP_EOL;
            $css .= '}'.PHP_EOL.PHP_EOL;
        }

        // Generate CSS for dark mode
        $css .= '[data-bs-theme="dark"] {'.PHP_EOL;
        foreach ($darkVariables as $key => $value) {
            $css .= "  {$key}: {$value};".PHP_EOL;
        }
        $css .= '}';

        return $css;
    }
}
