<?php

namespace Modules\Doctor\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum BloodType: int
{
    use OptimizeEnumTrait;

    case A_POSITIVE = 1;
    case A_NEGATIVE = 2;
    case B_POSITIVE = 3;
    case B_NEGATIVE = 4;
    case AB_POSITIVE = 5;
    case AB_NEGATIVE = 6;
    case O_POSITIVE = 7;
    case O_NEGATIVE = 8;

    public function label(): array|string
    {
        return trans('doctor::doctor.enum.BloodType.'.$this->value);
    }

    public function class(): array|string
    {
        return match ($this) {
            self::A_POSITIVE => 'primary',
            self::A_NEGATIVE => 'secondary',
            self::B_POSITIVE, self::O_NEGATIVE => 'warning',
            self::B_NEGATIVE => 'success',
            self::O_POSITIVE => 'danger',
            self::AB_POSITIVE => 'info',
            self::AB_NEGATIVE => 'dark',
        };
    }
}
