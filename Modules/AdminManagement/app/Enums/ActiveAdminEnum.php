<?php

namespace Modules\AdminManagement\Enums;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;

enum ActiveAdminEnum: int
{
    case ACTIVE = 1;
    case DE_ACTIVE = 0;

    public function label(): Application|array|string|Translator
    {
        return trans('adminmanagement::user_management.ActiveAdminEnum.'.$this->value);
    }

    public function class(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-primary',
            self::DE_ACTIVE => 'bg-danger',
        };
    }
}
