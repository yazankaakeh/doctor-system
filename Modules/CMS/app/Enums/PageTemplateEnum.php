<?php

namespace Modules\CMS\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PageTemplateEnum: string
{
    use OptimizeEnumTrait;

    case DEFAULT = 'default';
    case FULL_WIDTH = 'full_width';
    case LANDING = 'landing';
    case CONTACT = 'contact';

    public function label(): string
    {
        return trans('cms::enums.PageTemplateEnum.'.$this->value);
    }
}
