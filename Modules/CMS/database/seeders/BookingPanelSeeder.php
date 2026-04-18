<?php

namespace Modules\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CMS\Enums\PanelTypeEnum;
use Modules\CMS\Models\Page;
use Modules\CMS\Models\Panel;

class BookingPanelSeeder extends Seeder
{
    public function run(): void
    {
        $homePage = Page::query()->where('slug', 'home')->first();

        if (! $homePage) {
            $this->command->warn('Home page not found. Run LandingPageSeeder first.');

            return;
        }

        // Check if booking panel already exists
        $existingPanel = Panel::query()
            ->where('page_id', $homePage->id)
            ->where('type', PanelTypeEnum::BOOKING->value)
            ->first();

        if ($existingPanel) {
            $this->command->info('Booking panel already exists. Skipping...');

            return;
        }

        // Get the current max order
        $maxOrder = Panel::query()
            ->where('page_id', $homePage->id)
            ->max('order') ?? 0;

        // Create booking panel
        Panel::query()->create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::BOOKING->value,
            'title' => [
                'en' => 'Book Your Appointment',
                'ar' => 'احجز موعدك',
                'tr' => 'Randevunuzu Alın',
            ],
            'slug' => 'booking',
            'order' => $maxOrder + 1,
            'is_active' => true,
            'settings' => [
                'background_color' => '#f8f9fa',
                'text_color' => '#333333',
                'subtitle' => 'Schedule your consultation with our expert doctors in just a few clicks',
            ],
        ]);

        $this->command->info('Booking panel created successfully!');
    }
}
