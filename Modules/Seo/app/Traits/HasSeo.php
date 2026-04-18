<?php

namespace Modules\Seo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Seo\Models\SeoMeta;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
