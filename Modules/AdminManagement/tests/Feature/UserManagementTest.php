<?php

namespace Modules\AdminManagement\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\AdminManagement\Tests\AdminManagementTestCase;
use Modules\Core\App\Enums\Gender;

class UserManagementTest extends AdminManagementTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_cannot_access_user_management(): void
    {
        $response = $this->get(route('admin.user_management.index'));

        $response->assertRedirect();
    }

    public function test_authenticated_admin_can_view_user_list(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.user_management.index'));

        $response->assertOk();
        $response->assertViewIs('adminmanagement::users.index');
        $response->assertViewHas(['users', 'roles', 'medicalSpecialty']);
    }

    public function test_admin_can_create_new_doctor(): void
    {
        $doctorData = [
            'name' => 'New Doctor',
            'email' => 'newdoctor@hospital.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'gender' => Gender::MALE->value,
            'age' => 35,
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg', 100, 100),
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.user_management.store'), $doctorData);

        $response->assertRedirect(route('admin.user_management.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('doctors', [
            'email' => 'newdoctor@hospital.com',
            'name' => 'New Doctor',
        ]);
    }

    public function test_admin_can_update_existing_doctor(): void
    {
        $doctor = $this->createTestDoctor(['email' => 'existing@hospital.com']);

        $updateData = [
            'id' => $doctor->id,
            'name' => 'Updated Doctor Name',
            'email' => 'updated@hospital.com',
            'phone' => '9876543210',
            'gender' => Gender::FEMALE->value,
            'is_active' => 'on',
            'role' => $this->adminRole->id,
        ];

        $response = $this->actingAsAdmin()
            ->put(route('admin.user_management.update'), $updateData);

        $response->assertRedirect(route('admin.user_management.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'name' => 'Updated Doctor Name',
            'email' => 'updated@hospital.com',
        ]);
    }

    public function test_admin_can_toggle_doctor_status(): void
    {
        $doctor = $this->createTestDoctor([
            'email' => 'status@hospital.com',
            'is_active' => 1,
        ]);

        $response = $this->actingAsAdmin()
            ->delete(route('admin.user_management.status'), [
                'id' => $doctor->id,
                'is_active' => 'off',
            ]);

        $response->assertRedirect(route('admin.user_management.index'));
        $response->assertSessionHas('success');

        $doctor->refresh();
        $this->assertEquals(0, $doctor->is_active->value);
    }

    public function test_create_doctor_validation_requires_name(): void
    {
        $doctorData = [
            'email' => 'noname@hospital.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'gender' => Gender::MALE->value,
            'age' => 35,
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.user_management.store'), $doctorData);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_doctor_validation_requires_unique_email(): void
    {
        $existingDoctor = $this->createTestDoctor(['email' => 'duplicate@hospital.com']);

        $doctorData = [
            'name' => 'Duplicate Email Doctor',
            'email' => 'duplicate@hospital.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'gender' => Gender::MALE->value,
            'age' => 35,
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.user_management.store'), $doctorData);

        $response->assertSessionHasErrors('email');
    }

    public function test_create_doctor_validation_requires_password_confirmation(): void
    {
        $doctorData = [
            'name' => 'No Confirm Password',
            'email' => 'noconfirm@hospital.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'gender' => Gender::MALE->value,
            'age' => 35,
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.user_management.store'), $doctorData);

        $response->assertSessionHasErrors('password');
    }

    public function test_create_doctor_validation_requires_valid_age(): void
    {
        $doctorData = [
            'name' => 'Invalid Age Doctor',
            'email' => 'invalidage@hospital.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'gender' => Gender::MALE->value,
            'age' => 10, // Invalid - below 18
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.user_management.store'), $doctorData);

        $response->assertSessionHasErrors('age');
    }

    public function test_create_doctor_validation_requires_valid_medical_specialty(): void
    {
        $doctorData = [
            'name' => 'Invalid Specialty Doctor',
            'email' => 'invalidspecialty@hospital.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'gender' => Gender::MALE->value,
            'age' => 35,
            'medicalSpecialtyId' => 99999, // Invalid ID
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ];

        $response = $this->actingAsAdmin()
            ->post(route('admin.user_management.store'), $doctorData);

        $response->assertSessionHasErrors('medicalSpecialtyId');
    }

    public function test_update_doctor_allows_optional_password(): void
    {
        $doctor = $this->createTestDoctor(['email' => 'nopasswordchange@hospital.com']);
        $originalPassword = $doctor->password;

        $updateData = [
            'id' => $doctor->id,
            'name' => 'Updated Name Only',
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'gender' => $doctor->gender->value,
            'is_active' => 'on',
            'role' => $this->adminRole->id,
            // No password field
        ];

        $response = $this->actingAsAdmin()
            ->put(route('admin.user_management.update'), $updateData);

        $response->assertRedirect(route('admin.user_management.index'));

        $doctor->refresh();
        $this->assertEquals($originalPassword, $doctor->password);
    }

    public function test_view_user_list_shows_pagination(): void
    {
        // Create multiple doctors
        for ($i = 0; $i < 20; $i++) {
            $this->createTestDoctor(['email' => "doctor{$i}@hospital.com"]);
        }

        $response = $this->actingAsAdmin()
            ->get(route('admin.user_management.index'));

        $response->assertOk();
        $response->assertViewHas('users');
    }
}
