<?php

namespace Modules\Core\App\Enums;

use Illuminate\Contracts\Translation\Translator;
use Modules\Core\app\Traits\OptimizeEnumTrait;

enum Gender: int
{
    use OptimizeEnumTrait;

    case MALE = 1;
    case FEMALE = 0;

    public function label(): array|string|Translator
    {
        return trans('doctor::doctor.enum.Gender.'.$this->value);
    }

    public function class(): string
    {
        return match ($this) {
            self::MALE => 'primary',
            self::FEMALE => 'danger',
        };
    }
}
