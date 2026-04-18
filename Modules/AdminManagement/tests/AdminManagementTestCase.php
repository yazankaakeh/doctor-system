<?php

namespace Modules\AdminManagement\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\MedicalSpecialty;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class AdminManagementTestCase extends TestCase
{
    use RefreshDatabase;

    protected Doctor $adminUser;

    protected Role $adminRole;

    protected MedicalSpecialty $medicalSpecialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();
        $this->setupMedicalSpecialty();
        $this->setupAdminUser();
    }

    protected function setupPermissions(): void
    {
        // Create base permissions for testing
        $permissions = [
            'admin.user_management.index',
            'admin.user_management.store',
            'admin.user_management.update',
            'admin.user_management.status',
            'admin.role_management.index',
            'admin.role_management.create',
            'admin.role_management.store',
            'admin.role_management.edit',
            'admin.role_management.update',
            'admin.role_management.destroy',
            'admin.audits.index',
            'admin.audits.getPayload',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'doctor',
            ]);
        }

        $this->adminRole = Role::create([
            'name' => 'SUPER_ADMIN',
            'guard_name' => 'doctor',
        ]);

        $this->adminRole->givePermissionTo($permissions);
    }

    protected function setupMedicalSpecialty(): void
    {
        $this->medicalSpecialty = MedicalSpecialty::factory()->create([
            'name' => 'General Medicine',
        ]);
    }

    protected function setupAdminUser(): void
    {
        $this->adminUser = Doctor::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'is_active' => 1,
            'medical_specialty_id' => $this->medicalSpecialty->id,
        ]);

        $this->adminUser->assignRole($this->adminRole);
    }

    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->adminUser, 'doctor');
    }

    protected function createTestDoctor(array $attributes = []): Doctor
    {
        $doctor = Doctor::factory()->create(array_merge([
            'medical_specialty_id' => $this->medicalSpecialty->id,
            'phone' => fake()->numerify('##########'),
        ], $attributes));

        $doctor->assignRole($this->adminRole);

        return $doctor;
    }

    protected function createTestRole(string $name = 'Test Role', array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'doctor',
        ]);

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }
}
