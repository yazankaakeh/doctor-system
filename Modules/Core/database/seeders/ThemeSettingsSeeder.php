<?php

namespace Modules\Core\database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\App\Models\ThemeSetting;

class ThemeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Admin Theme Settings
        ThemeSetting::query()->updateOrCreate(
            ['scope' => 'admin'],
            [
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
                'custom_css' => '',
                'dark_custom_css' => '',
                'is_active' => true,
            ]
        );

        // Seed Website Theme Settings
        ThemeSetting::query()->updateOrCreate(
            ['scope' => 'website'],
            [
                // Light mode colors
                'primary_color' => '#0F0F2D',
                'secondary_color' => '#6c757d',
                'success_color' => '#28a745',
                'info_color' => '#17a2b8',
                'warning_color' => '#ffc107',
                'danger_color' => '#dc3545',
                // Dark mode colors
                'dark_primary_color' => '#696cff',
                'dark_secondary_color' => '#8592a3',
                'dark_success_color' => '#71dd37',
                'dark_info_color' => '#03c3ec',
                'dark_warning_color' => '#ffab00',
                'dark_danger_color' => '#ff3e1d',
                // Typography
                'font_family' => 'Poppins',
                'font_size_base' => '1rem',
                'headings_font_family' => 'Poppins',
                'headings_font_weight' => '600',
                // Layout
                'body_bg' => '#ffffff',
                'card_bg' => '#f8f9fa',
                'dark_body_bg' => '#1a1a2e',
                'dark_card_bg' => '#16213e',
                'border_radius' => '0.5rem',
                'site_title' => 'Base Project',
                'custom_css' => '',
                'dark_custom_css' => '',
                'is_active' => true,
            ]
        );

        $this->command->info('Theme settings seeded successfully!');
    }
}
