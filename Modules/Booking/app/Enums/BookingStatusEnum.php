<?php

namespace Modules\Booking\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum BookingStatusEnum: int
{
    use OptimizeEnumTrait;

    case PENDING = 1;
    case CONFIRMED = 2;
    case CANCELLED = 3;
    case COMPLETED = 4;
    case NO_SHOW = 5;

    public function label(): array|string
    {
        return trans('booking::booking.enum.BookingStatusEnum.'.$this->value);
    }

    public function class(): array|string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'primary',
            self::CANCELLED => 'danger',
            self::COMPLETED => 'success',
            self::NO_SHOW => 'secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::CONFIRMED => 'check-circle',
            self::CANCELLED => 'x-circle',
            self::COMPLETED => 'check-double',
            self::NO_SHOW => 'user-x',
        };
    }

    public static function activeStatuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
        ];
    }
}
