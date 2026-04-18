<?php

namespace Modules\Payment\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PaymentMethodEnum: string
{
    use OptimizeEnumTrait;

    case PAYPAL = 'paypal';
    case OFFLINE = 'offline';
    case CREDIT_CARD = 'credit_card';

    public function label(): array|string
    {
        return trans('payment::payment.enum.PaymentMethodEnum.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::PAYPAL => 'paypal',
            self::OFFLINE => 'building-bank',
            self::CREDIT_CARD => 'credit-card',
        };
    }

    public function description(): string
    {
        return trans('payment::payment.enum.PaymentMethodDescription.'.$this->value);
    }
}
