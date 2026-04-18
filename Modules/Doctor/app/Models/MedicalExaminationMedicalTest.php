<?php

namespace Modules\Doctor\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;

class MedicalExaminationMedicalTest extends Pivot implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'medical_examination_medical_test';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'medical_examination_id',
        'medical_test_id',
        'value',
    ];

    public function medicalTest(): HasOne
    {
        return $this->hasOne(MedicalTest::class, 'id', 'medical_test_id');
    }

    public function medicalExamination(): HasOne
    {
        return $this->hasOne(MedicalExamination::class, 'id', 'medical_examination_id');
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('attachment')
            ->acceptsFile(function (File $file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'application/pdf',
                ], true);
            })
            ->singleFile()
            ->useDisk('secure'); // Store medical test files in secure (private) storage
    }

    /**
     * Get secure URL for media download.
     */
    public function getSecureMediaUrl($mediaItem): string
    {
        return route('secure-file.download', ['mediaId' => $mediaItem->id]);
    }
}
