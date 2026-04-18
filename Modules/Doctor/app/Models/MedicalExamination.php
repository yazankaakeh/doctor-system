<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Doctor\Database\Factories\MedicalExaminationFactory;
use Modules\Doctor\Enums\MedicalExaminationStatusEnum;
use Modules\Doctor\Enums\MedicalTestTypeEnum;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

/**
 * @property Patient $patient
 */
class MedicalExamination extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'doctor_id',
        'patient_id',
        'clinic_id',
        'reason_of_visiting',
        'clinical_examination',
        'impression',
        'request_for_action',
        'medical_story',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => MedicalExaminationStatusEnum::class,
    ];

    public static function patientMedicalExaminationsWithoutId($patientId, $id = null): Builder
    {
        return self::query()->where('patient_id', $patientId)
            ->when($id ?? null, fn ($q, $v) => $q->whereNot('id', $v))
            ->with('vitalSigns')
            ->with('medicines')
            ->with('medicalTests')
            ->with('finalDiagnosis')->with('media');
    }

    protected static function newFactory(): MedicalExaminationFactory
    {
        return MedicalExaminationFactory::new();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function vitalSigns(): BelongsToMany
    {
        return $this
            ->belongsToMany(VitalSign::class, 'medical_examination_vital_sign')
            ->withPivot('value')
            ->withTimestamps()
            ->using(MedicalExaminationVitalSign::class); // optional
    }

    public function medicines(): BelongsToMany
    {
        return $this
            ->belongsToMany(Medicine::class, 'medical_examination_medicine')
            ->withPivot('dose')
            ->withPivot('dosage')
            ->withPivot('dosage_form_id')
            ->withPivot('duration')
            ->withPivot('note')
            ->withTimestamps()
            ->using(MedicalExaminationMedicine::class); // optional
    }

    public function laboratoryTests(): BelongsToMany
    {
        return $this
            ->medicalTests()
            ->where('medical_tests.type', MedicalTestTypeEnum::LABORATORY_TESTS->value);
    }

    public function medicalTests(): BelongsToMany
    {
        return $this
            ->belongsToMany(MedicalTest::class, 'medical_examination_medical_test')
            ->using(MedicalExaminationMedicalTest::class)
            ->withPivot('value', 'id');
    }

    public function finalDiagnosis(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                FinalDiagnosis::class,
                'final_diagnosis_patients',
                'medical_examination_id',
                'final_diagnosis_id',
            )
            ->using(FinalDiagnosisPatient::class)
            ->withPivot(['patient_id'])
            ->withTimestamps();
    }

    public function radiologyTests(): BelongsToMany
    {
        return $this
            ->medicalTests()
            ->where('medical_tests.type', MedicalTestTypeEnum::RADIOLOGY_TESTS->value);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('attachments')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                ], true);
            })
            ->useDisk('secure'); // Store medical examination files in secure (private) storage
    }

    /**
     * Get secure URL for media download.
     */
    public function getSecureMediaUrl($mediaItem): string
    {
        return route('secure-file.download', ['mediaId' => $mediaItem->id]);
    }
}
