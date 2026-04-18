<?php

namespace Modules\CMS\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PanelItemTypeEnum: string
{
    use OptimizeEnumTrait;

    case FEATURE_CARD = 'feature_card';
    case TEAM_MEMBER = 'team_member';
    case REVIEW = 'review';
    case FAQ_ITEM = 'faq_item';
    case STAT = 'stat';
    case GALLERY_IMAGE = 'gallery_image';
    case CAROUSEL_SLIDE = 'carousel_slide';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::FEATURE_CARD => 'Feature Card',
            self::TEAM_MEMBER => 'Team Member',
            self::REVIEW => 'Review / Testimonial',
            self::FAQ_ITEM => 'FAQ Item',
            self::STAT => 'Statistic',
            self::GALLERY_IMAGE => 'Gallery Image',
            self::CAROUSEL_SLIDE => 'Carousel Slide',
            self::CUSTOM => 'Custom Item',
        };
    }

    public function fields(): array
    {
        return match ($this) {
            self::FEATURE_CARD => ['icon', 'title', 'description'],
            self::TEAM_MEMBER => ['image', 'name', 'role', 'bio', 'social_links'],
            self::REVIEW => ['image', 'name', 'role', 'rating', 'content'],
            self::FAQ_ITEM => ['question', 'answer'],
            self::STAT => ['icon', 'number', 'label', 'suffix'],
            self::GALLERY_IMAGE => ['image', 'caption'],
            self::CAROUSEL_SLIDE => ['image', 'title', 'subtitle', 'description', 'button_text', 'button_url', 'overlay_color'],
            self::CUSTOM => ['html_content'],
        };
    }
}
