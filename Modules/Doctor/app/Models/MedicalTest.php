<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Database\Factories\MedicalTestFactory;
use Modules\Doctor\Enums\MedicalTestTypeEnum;

class MedicalTest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'name',
        'unit',
        'is_active',
        'type',
    ];

    protected $casts = [
        'is_active' => ActiveEnum::class,
        'type' => MedicalTestTypeEnum::class,
    ];

    public static function getLaboratorySelect2(): Collection
    {
        return MedicalTest::query()->where('type', MedicalTestTypeEnum::LABORATORY_TESTS->value)->pluck('name', 'id');
    }

    public static function getRadiologySelect2(): Collection
    {
        return MedicalTest::query()->where('type', MedicalTestTypeEnum::RADIOLOGY_TESTS->value)->pluck('name', 'id');
    }

    protected static function newFactory(): MedicalTestFactory
    {
        return MedicalTestFactory::new();
    }

    public function medicalExaminationMedicalTest()
    {
        return $this->hasMany(MedicalExaminationMedicalTest::class, 'medical_test_id');
    }

    public function medicalExaminations(): BelongsToMany
    {
        return $this
            ->belongsToMany(MedicalExamination::class, 'medical_examination_medical_test')
            ->withPivot('value')
            ->using(MedicalExaminationMedicalTest::class)->with('media'); // optional
    }
}
