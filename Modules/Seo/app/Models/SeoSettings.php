<?php

namespace Modules\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Seo\Enums\SeoSettingsEnum;

class SeoSettings extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    /**
     * Update or create SEO setting by key and value
     *
     * @param  string  $key  The setting key (must be from SeoSettingsEnum)
     * @param  string|null  $value  The setting value
     * @return SeoSettings
     *
     * @throws \InvalidArgumentException If key is not valid
     *
     * @example
     * SeoSettings::put('site_name', 'My Website');
     * SeoSettings::put('twitter_site', '@mywebsite');
     */
    public static function put($key, $value)
    {
        // Validate that the key exists in the enum
        $validKeys = array_column(SeoSettingsEnum::cases(), 'value');
        if (! in_array($key, $validKeys)) {
            throw new \InvalidArgumentException("Invalid SEO setting key: {$key}");
        }

        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Update multiple SEO settings at once
     *
     * @param  array  $settings  Array of key-value pairs
     * @return array Array of updated SeoSettings models
     *
     * @example
     * SeoSettings::updateSettings([
     *     'site_name' => 'My Website',
     *     'twitter_site' => '@mywebsite',
     *     'facebook' => 'https://facebook.com/mywebsite'
     * ]);
     */
    public static function updateSettings(array $settings)
    {
        $validKeys = array_column(SeoSettingsEnum::cases(), 'value');
        $updated = [];

        foreach ($settings as $key => $value) {
            // Only update valid keys
            if (in_array($key, $validKeys)) {
                $updated[$key] = static::put($key, $value);
            }
        }

        return $updated;
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}
