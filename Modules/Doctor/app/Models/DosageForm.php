<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Core\App\Enums\ActiveEnum;
use Spatie\Translatable\HasTranslations;

class DosageForm extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => ActiveEnum::class,
    ];

    public static function getDosageFormSelect2(): Collection
    {
        return DosageForm::query()->where('is_active', ActiveEnum::ACTIVE)->pluck('name', 'id');
    }
}
