<?php

namespace Modules\Doctor\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Core\app\Models\Country;
use Modules\Doctor\Models\Clinic;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Modules\Doctor\Models\Patient;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class DoctorTestCase extends TestCase
{
    use RefreshDatabase;

    protected Doctor $doctor;

    protected Role $doctorRole;

    protected MedicalSpecialty $medicalSpecialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupCountriesTable();
        $this->setupPermissions();
        $this->setupMedicalSpecialty();
        $this->setupDoctor();
    }

    protected function setupCountriesTable(): void
    {
        // Create countries table if it doesn't exist (for testing)
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        // Create a test country
        Country::firstOrCreate(['id' => 1], ['name' => 'Test Country']);
    }

    protected function setupPermissions(): void
    {
        $permissions = [
            'doctor.patients.index' => 'Patients',
            'doctor.patients.store' => 'Patients',
            'doctor.patients.update' => 'Patients',
            'doctor.patients.show' => 'Patients',
            'doctor.patients.downloadVCard' => 'Patients',
            'doctor.clinic.index' => 'Clinics',
            'doctor.clinic.store' => 'Clinics',
            'doctor.clinic.update' => 'Clinics',
            'doctor.medicalExamination.index' => 'Medical Examination',
            'doctor.medicalExamination.create' => 'Medical Examination',
            'doctor.medicalExamination.store' => 'Medical Examination',
            'doctor.medicalExamination.show' => 'Medical Examination',
            'doctor.medicine.index' => 'Medicine',
            'doctor.medicine.store' => 'Medicine',
            'doctor.medicine.update' => 'Medicine',
            'doctor.medicalTest.index' => 'Medical Test',
            'doctor.medicalTest.store' => 'Medical Test',
            'doctor.medicalTest.update' => 'Medical Test',
            'doctor.vitalSign.index' => 'Vital Sign',
            'doctor.vitalSign.store' => 'Vital Sign',
            'doctor.vitalSign.update' => 'Vital Sign',
            'doctor.dashboard' => 'Dashboard',
            'doctor.profile.index' => 'Profile',
            'doctor.profile.update' => 'Profile',
        ];

        foreach ($permissions as $permission => $section) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'doctor',
                'section' => $section,
            ]);
        }

        $this->doctorRole = Role::create([
            'name' => 'DOCTOR',
            'guard_name' => 'doctor',
        ]);

        $this->doctorRole->givePermissionTo(array_keys($permissions));
    }

    protected function setupMedicalSpecialty(): void
    {
        $this->medicalSpecialty = MedicalSpecialty::factory()->create([
            'name' => 'General Medicine',
        ]);
    }

    protected function setupDoctor(): void
    {
        $this->doctor = Doctor::factory()->create([
            'name' => 'Test Doctor',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'is_active' => ActiveEnum::ACTIVE,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->doctor->assignRole($this->doctorRole);
    }

    protected function actingAsDoctor(): self
    {
        return $this->actingAs($this->doctor, 'doctor');
    }

    protected function createPatient(array $attributes = []): Patient
    {
        return Patient::factory()->create(array_merge([
            'is_active' => ActiveEnum::ACTIVE,
        ], $attributes));
    }

    protected function createClinic(array $attributes = []): Clinic
    {
        return Clinic::factory()->create(array_merge([
            'is_active' => 1,
        ], $attributes));
    }
}
