<?php

namespace Tests\Feature\NonFunctional;

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

/**
 * Shared base class for non-functional test suites.
 *
 * Non-functional tests cover the quality attributes of the system
 * (security, performance, availability, usability, maintainability,
 * scalability and portability) as opposed to feature correctness.
 *
 * This base case sets up just enough scaffolding (roles, permissions,
 * a default active doctor and medical specialty) to exercise the
 * doctor area end-to-end without depending on the much larger
 * per-module test cases.
 */
abstract class NonFunctionalTestCase extends TestCase
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
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        Country::firstOrCreate(['id' => 1], ['name' => 'Test Country']);
    }

    protected function setupPermissions(): void
    {
        $permissions = [
            'doctor.dashboard' => 'Dashboard',
            'doctor.patients.index' => 'Patients',
            'doctor.patients.store' => 'Patients',
            'doctor.patients.update' => 'Patients',
            'doctor.patients.show' => 'Patients',
            'doctor.clinic.index' => 'Clinics',
            'doctor.clinic.store' => 'Clinics',
            'doctor.clinic.update' => 'Clinics',
            'doctor.medicalExamination.index' => 'Medical Examination',
            'doctor.medicalExamination.create' => 'Medical Examination',
            'doctor.medicalExamination.store' => 'Medical Examination',
            'doctor.medicalExamination.show' => 'Medical Examination',
            'doctor.profile.index' => 'Profile',
            'doctor.profile.update' => 'Profile',
        ];

        foreach ($permissions as $permission => $section) {
            Permission::findOrCreate($permission, 'doctor');
        }

        $this->doctorRole = Role::firstOrCreate(
            ['name' => 'DOCTOR', 'guard_name' => 'doctor']
        );

        $this->doctorRole->syncPermissions(array_keys($permissions));
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
            'name' => 'NFR Doctor',
            'email' => 'nfr-doctor@test.com',
            'password' => bcrypt('password'),
            'is_active' => ActiveEnum::ACTIVE,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->doctor->assignRole($this->doctorRole);
    }

    protected function actingAsDoctor(): self
    {
        return $this->actingAs($this->doctor, 'doctor');
    }

    protected function createClinic(array $attributes = []): Clinic
    {
        return Clinic::factory()->create(array_merge([
            'is_active' => 1,
        ], $attributes));
    }

    protected function createPatient(array $attributes = []): Patient
    {
        return Patient::factory()->create(array_merge([
            'is_active' => ActiveEnum::ACTIVE,
            'password' => bcrypt('password'),
        ], $attributes));
    }

    /**
     * Absolute project root path used by tests that inspect source files.
     */
    protected function projectRoot(): string
    {
        return realpath(__DIR__.'/../../..');
    }
}
