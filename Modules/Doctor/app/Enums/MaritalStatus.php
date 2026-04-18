<?php

namespace Modules\Doctor\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum MaritalStatus: int
{
    use OptimizeEnumTrait;

    case SINGLE = 1;
    case MARRIED = 2;
    case DIVORCED = 3;

    public function label(): array|string
    {
        return trans('doctor::doctor.enum.MaritalStatus.'.$this->value);
    }

    public function class(): array|string
    {
        return match ($this) {
            self::SINGLE => 'primary',
            self::MARRIED => 'success',
            self::DIVORCED => 'warning',
        };
    }
}
