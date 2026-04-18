<?php

namespace Modules\CMS\Enums;

use Modules\Core\app\Traits\OptimizeEnumTrait;

enum PageStatusEnum: string
{
    use OptimizeEnumTrait;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return trans('cms::enums.PageStatusEnum.'.$this->value);
    }

    public function class(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'secondary',
        };
    }
}
