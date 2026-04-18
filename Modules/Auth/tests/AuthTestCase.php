<?php

namespace Modules\Auth\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\App\Enums\ActiveEnum;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Modules\Doctor\Models\Patient;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class AuthTestCase extends TestCase
{
    use RefreshDatabase;

    protected MedicalSpecialty $medicalSpecialty;

    protected Role $doctorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();
        $this->setupMedicalSpecialty();
    }

    protected function setupPermissions(): void
    {
        // Create minimal permissions for auth tests
        $permissions = [
            'doctor.dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'doctor',
                'section' => 'Auth',
            ]);
        }

        $this->doctorRole = Role::create([
            'name' => 'DOCTOR',
            'guard_name' => 'doctor',
        ]);

        $this->doctorRole->givePermissionTo($permissions);
    }

    protected function setupMedicalSpecialty(): void
    {
        $this->medicalSpecialty = MedicalSpecialty::factory()->create([
            'name' => 'General Medicine',
        ]);
    }

    protected function createActiveDoctor(array $attributes = []): Doctor
    {
        $doctor = Doctor::factory()->create(array_merge([
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'is_active' => ActiveEnum::ACTIVE,
            'password' => bcrypt('password'),
        ], $attributes));

        $doctor->assignRole($this->doctorRole);

        return $doctor;
    }

    protected function createInactiveDoctor(array $attributes = []): Doctor
    {
        $doctor = Doctor::factory()->create(array_merge([
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'is_active' => ActiveEnum::INACTIVE,
            'password' => bcrypt('password'),
        ], $attributes));

        $doctor->assignRole($this->doctorRole);

        return $doctor;
    }

    protected function createPatient(array $attributes = []): Patient
    {
        return Patient::factory()->create(array_merge([
            'password' => bcrypt('password'),
            'is_active' => ActiveEnum::ACTIVE,
        ], $attributes));
    }
}
