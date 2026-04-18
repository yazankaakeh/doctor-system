<?php

namespace Modules\Payment\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PaymentMethodEnum: string
{
    use OptimizeEnumTrait;

    case PAYPAL = 'paypal';
    case OFFLINE = 'offline';

    public function label(): array|string
    {
        return trans('payment::payment.enum.PaymentMethodEnum.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::PAYPAL => 'paypal',
            self::OFFLINE => 'credit-card',
        };
    }

    public function description(): string
    {
        return trans('payment::payment.enum.PaymentMethodDescription.'.$this->value);
    }
}
