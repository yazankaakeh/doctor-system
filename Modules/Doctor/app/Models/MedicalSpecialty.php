<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Database\Factories\MedicalSpecialtyFactory;
use Spatie\Translatable\HasTranslations;

/**
 * @property mixed $id
 */
class MedicalSpecialty extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['name'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'code' => 'string',
        'is_active' => ActiveEnum::class,
    ];

    public static function getMedicalSpecialtySelect2(): Collection
    {
        return self::query()->where('is_active', ActiveEnum::ACTIVE)->pluck('name', 'id');
    }

    protected static function newFactory(): MedicalSpecialtyFactory
    {
        return MedicalSpecialtyFactory::new();
    }
}
