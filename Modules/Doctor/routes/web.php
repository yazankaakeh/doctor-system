<?php

use Illuminate\Support\Facades\Route;
use Modules\Doctor\Http\Controllers\ClinicController;
use Modules\Doctor\Http\Controllers\DashboardController;
use Modules\Doctor\Http\Controllers\DosageFormController;
use Modules\Doctor\Http\Controllers\FinalDiagnosisController;
use Modules\Doctor\Http\Controllers\MedicalExaminationController;
use Modules\Doctor\Http\Controllers\MedicalSpecialtyController;
use Modules\Doctor\Http\Controllers\MedicalTestController;
use Modules\Doctor\Http\Controllers\MedicineController;
use Modules\Doctor\Http\Controllers\PatientController;
use Modules\Doctor\Http\Controllers\PDFController;
use Modules\Doctor\Http\Controllers\ProfileController;
use Modules\Doctor\Http\Controllers\UploadFileController;
use Modules\Doctor\Http\Controllers\VitalSignController;

Route::middleware(['auth:doctor', 'audit', 'admin-enabled', 'authorize', 'setLocale', 'doctorMenu'])->name(
    'doctor.',
)->prefix(
    'doctor',
)->group(
    function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile routes
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/clinic', [ClinicController::class, 'index'])->name('clinic.index');

        Route::post('/clinic/store', [ClinicController::class, 'store'])->name(
            'clinic.store',
        );

        Route::post('/clinic/update', [ClinicController::class, 'update'])->name(
            'clinic.update',
        );

        Route::get('/patients', [PatientController::class, 'index'])->name(
            'patients.index',
        );
        Route::get('/downloadVCard/{id}', [PatientController::class, 'downloadVCard'])->name(
            'patients.downloadVCard',
        );

        Route::post('/patients/store', [PatientController::class, 'store'])->name(
            'patients.store',
        );

        Route::post('/patients/update', [PatientController::class, 'update'])->name(
            'patients.update',
        );
        Route::get('/patients/show/{id}', [PatientController::class, 'show'])->name(
            'patients.show',
        );

        Route::get('/medicalTest', [MedicalTestController::class, 'index'])->name(
            'medicalTest.index',
        );
        Route::post('/medicalTest/store', [MedicalTestController::class, 'store'])->name(
            'medicalTest.store',
        );
        Route::post('/medicalTest/update', [MedicalTestController::class, 'update'])->name(
            'medicalTest.update',
        );

        Route::get('/medicine', [MedicineController::class, 'index'])->name(
            'medicine.index',
        );

        Route::post('/medicine/store', [MedicineController::class, 'store'])->name(
            'medicine.store',
        );

        Route::post('/medicine/update', [MedicineController::class, 'update'])->name(
            'medicine.update',
        );

        Route::get('/medicalSpecialty', [MedicalSpecialtyController::class, 'index'])->name(
            'medicalSpecialty.index',
        );
        Route::post('/medicalSpecialty/store', [MedicalSpecialtyController::class, 'store'])->name(
            'medicalSpecialty.store',
        );
        Route::post('/medicalSpecialty/update', [MedicalSpecialtyController::class, 'update'])->name(
            'medicalSpecialty.update',
        );

        Route::get('/vitalSign', [VitalSignController::class, 'index'])->name(
            'vitalSign.index',
        );
        Route::post('/vitalSign/store', [VitalSignController::class, 'store'])->name(
            'vitalSign.store',
        );
        Route::post('/vitalSign/update', [VitalSignController::class, 'update'])->name(
            'vitalSign.update',
        );

        Route::get('/finalDiagnosis', [FinalDiagnosisController::class, 'index'])->name(
            'finalDiagnosis.index',
        );
        Route::post('/finalDiagnosis/store', [FinalDiagnosisController::class, 'store'])->name(
            'finalDiagnosis.store',
        );
        Route::post('/finalDiagnosis/update', [FinalDiagnosisController::class, 'update'])->name(
            'finalDiagnosis.update',
        );

        Route::get('/dosageForm', [DosageFormController::class, 'index'])
            ->name(
                'dosageForm.index',
            );
        Route::post('/dosageForm/store', [DosageFormController::class, 'store'])
            ->name(
                'dosageForm.store',
            );
        Route::post(
            '/dosageForm/update',
            [DosageFormController::class, 'update'],
        )->name(
            'dosageForm.update',
        );

        Route::get(
            '/medicalExamination/create/{medicalExaminationId}',
            [MedicalExaminationController::class, 'create'],
        )->name(
            'medicalExamination.create',
        );
        Route::post(
            '/medicalExamination/store/{patientId}',
            [MedicalExaminationController::class, 'store'],
        )->name(
            'medicalExamination.store',
        );

        Route::post(
            '/medicalExamination/store',
            [MedicalExaminationController::class, 'submit'],
        )->name(
            'medicalExamination.submit',
        );

        Route::get(
            '/medicalExamination/show/{id}',
            [MedicalExaminationController::class, 'show'],
        )->name(
            'medicalExamination.show',
        );

        Route::get(
            '/medicalExamination/index',
            [MedicalExaminationController::class, 'index'],
        )->name(
            'medicalExamination.index',
        );

        Route::post(
            '/uploadFile/index',
            [UploadFileController::class, 'store'],
        )->name(
            'uploadFile.index',
        );

        Route::post(
            '/uploadFile/delete/{id}',
            [UploadFileController::class, 'delete'],
        )->name(
            'uploadFile.delete',
        );

        Route::get(
            '/pdf/downloadMedicines/{id}/{pageSize?}',
            [PDFController::class, 'downloadMedicines'],
        )->where([
            'id' => '[0-9]+',
            'pageSize' => 'A4|A3|A5',
        ])->name(
            'pdf.downloadMedicines',
        );
        Route::get(
            '/pdf/downloadMedicalTest/{id}/{pageSize?}',
            [PDFController::class, 'downloadMedicalTest'],
        )->where([
            'id' => '[0-9]+',
            'pageSize' => 'A4|A3|A5',
        ])->name(
            'pdf.downloadMedicalTest',
        );
        Route::get(
            '/pdf/downloadMedicinesPharmacy/{id}/{pageSize?}',
            [PDFController::class, 'downloadMedicinesPharmacy'],
        )->where([
            'id' => '[0-9]+',
            'pageSize' => 'A4|A3|A5',
        ])->name(
            'pdf.downloadMedicinesPharmacy',
        );
    },
);
