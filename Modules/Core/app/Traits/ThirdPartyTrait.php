<?php

namespace Modules\Core\App\Traits;

use Modules\Core\App\Models\ThirdPartyLog;

trait ThirdPartyTrait
{
    public function saveLog($model, $data): void
    {
        ThirdPartyLog::query()->create([
            'loggable_id' => $model->id,
            'loggable_tye' => get_class($model),
            'type' => $data['type'],
            'body' => $data['body'],
        ]);
    }
}
