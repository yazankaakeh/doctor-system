<?php

namespace Modules\Core\App\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum LanguageEnum: string
{
    use OptimizeEnumTrait;

    case EN = 'en';
    case AR = 'ar';
    case TR = 'tr';
}
