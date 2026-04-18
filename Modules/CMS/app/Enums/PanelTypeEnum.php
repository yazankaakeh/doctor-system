<?php

namespace Modules\CMS\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PanelTypeEnum: string
{
    use OptimizeEnumTrait;

    case HERO = 'hero';
    case FEATURES = 'features';
    case TEAM = 'team';
    case REVIEWS = 'reviews';
    case FAQ = 'faq';
    case CTA = 'cta';
    case CONTACT = 'contact';
    case STATS = 'stats';
    case GALLERY = 'gallery';
    case CAROUSEL = 'carousel';
    case BOOKING = 'booking';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::HERO => 'Hero Section',
            self::FEATURES => 'Features',
            self::TEAM => 'Team Members',
            self::REVIEWS => 'Reviews / Testimonials',
            self::FAQ => 'FAQ',
            self::CTA => 'Call to Action',
            self::CONTACT => 'Contact Form',
            self::STATS => 'Statistics / Numbers',
            self::GALLERY => 'Image Gallery',
            self::CAROUSEL => 'Carousel / Slider',
            self::BOOKING => 'Booking Widget',
            self::CUSTOM => 'Custom HTML',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::HERO => 'Large header section with title, subtitle, and CTA buttons',
            self::FEATURES => 'Grid of feature cards with icons, titles, and descriptions',
            self::TEAM => 'Team member profiles with photos, names, and roles',
            self::REVIEWS => 'Customer reviews and testimonials',
            self::FAQ => 'Frequently asked questions with collapsible answers',
            self::CTA => 'Call-to-action section with button',
            self::CONTACT => 'Contact form with customizable fields',
            self::STATS => 'Statistics or number counters',
            self::GALLERY => 'Image gallery with lightbox',
            self::CAROUSEL => 'Modern image/content slider with autoplay and navigation',
            self::BOOKING => 'Appointment booking wizard for patients',
            self::CUSTOM => 'Custom HTML content',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::HERO => 'tabler-rocket',
            self::FEATURES => 'tabler-list-check',
            self::TEAM => 'tabler-users',
            self::REVIEWS => 'tabler-message-star',
            self::FAQ => 'tabler-help',
            self::CTA => 'tabler-click',
            self::CONTACT => 'tabler-mail',
            self::STATS => 'tabler-chart-bar',
            self::GALLERY => 'tabler-photo',
            self::CAROUSEL => 'tabler-carousel-horizontal',
            self::BOOKING => 'tabler-calendar-event',
            self::CUSTOM => 'tabler-code',
        };
    }

    public function hasItems(): bool
    {
        return in_array($this, [
            self::FEATURES,
            self::TEAM,
            self::REVIEWS,
            self::FAQ,
            self::STATS,
            self::GALLERY,
            self::CAROUSEL,
        ], true);
    }

    public function maxItems(): ?int
    {
        return match ($this) {
            self::HERO, self::CONTACT, self::CTA, self::BOOKING => 1,
            default => null, // Unlimited
        };
    }
}
