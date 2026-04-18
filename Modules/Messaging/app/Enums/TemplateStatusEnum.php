<?php

namespace Modules\Messaging\Enums;

enum TemplateStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DISABLED = 'disabled';
    case PAUSED = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('messaging::template.status.pending'),
            self::APPROVED => __('messaging::template.status.approved'),
            self::REJECTED => __('messaging::template.status.rejected'),
            self::DISABLED => __('messaging::template.status.disabled'),
            self::PAUSED => __('messaging::template.status.paused'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::DISABLED => 'secondary',
            self::PAUSED => 'info',
        };
    }

    public function canSend(): bool
    {
        return $this === self::APPROVED;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
