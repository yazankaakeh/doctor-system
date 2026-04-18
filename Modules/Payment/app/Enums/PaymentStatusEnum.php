<?php

namespace Modules\Payment\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PaymentStatusEnum: int
{
    use OptimizeEnumTrait;

    case PENDING = 1;
    case COMPLETED = 2;
    case FAILED = 3;
    case REFUNDED = 4;
    case AWAITING_VERIFICATION = 5;

    public function label(): array|string
    {
        return trans('payment::payment.enum.PaymentStatusEnum.'.$this->value);
    }

    public function class(): array|string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
            self::AWAITING_VERIFICATION => 'secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'clock',
            self::COMPLETED => 'check-circle',
            self::FAILED => 'x-circle',
            self::REFUNDED => 'rotate-ccw',
            self::AWAITING_VERIFICATION => 'eye',
        };
    }
}
