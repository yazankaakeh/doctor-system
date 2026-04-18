<?php

namespace Modules\AdminManagement\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\AdminManagement\Http\Requests\DoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateDoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateStatusAminRequest;
use Modules\AdminManagement\Repository\User\DoctorRepository;
use Modules\AdminManagement\Tests\AdminManagementTestCase;
use Modules\Core\App\Enums\Gender;
use Modules\Doctor\Models\Doctor;

class DoctorRepositoryTest extends AdminManagementTestCase
{
    private DoctorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DoctorRepository;
        Storage::fake('public');
    }

    public function test_index_returns_users_and_roles(): void
    {
        $this->createTestDoctor();
        $this->createTestDoctor(['email' => 'another@test.com']);

        $result = $this->repository->index();

        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('roles', $result);
        $this->assertGreaterThanOrEqual(2, $result['users']->count());
    }

    public function test_store_creates_new_doctor(): void
    {
        $request = $this->createDoctorRequest([
            'name' => 'New Doctor',
            'email' => 'newdoctor@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0987654321',
            'gender' => Gender::MALE->value,
            'age' => 30,
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ]);

        $this->repository->store($request);

        $this->assertDatabaseHas('doctors', [
            'email' => 'newdoctor@test.com',
            'name' => 'New Doctor',
        ]);
    }

    public function test_store_assigns_role_to_doctor(): void
    {
        $request = $this->createDoctorRequest([
            'name' => 'Role Test Doctor',
            'email' => 'roletest@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0987654322',
            'gender' => Gender::MALE->value,
            'age' => 35,
            'medicalSpecialtyId' => $this->medicalSpecialty->id,
            'role' => $this->adminRole->id,
            'is_active' => 'on',
            'img' => UploadedFile::fake()->image('doctor.jpg'),
        ]);

        $this->repository->store($request);

        $doctor = Doctor::where('email', 'roletest@test.com')->first();
        $this->assertTrue($doctor->hasRole('SUPER_ADMIN'));
    }

    public function test_update_modifies_existing_doctor(): void
    {
        $doctor = $this->createTestDoctor(['email' => 'update@test.com']);

        $request = $this->createUpdateDoctorRequest([
            'id' => $doctor->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
            'phone' => '1111111111',
            'gender' => Gender::FEMALE->value,
            'is_active' => 'on',
            'role' => $this->adminRole->id,
        ]);

        $this->repository->update($request);

        $doctor->refresh();
        $this->assertEquals('Updated Name', $doctor->name);
        $this->assertEquals('updated@test.com', $doctor->email);
    }

    public function test_update_changes_password_when_provided(): void
    {
        $doctor = $this->createTestDoctor(['email' => 'pwdupdate@test.com']);
        $oldPassword = $doctor->password;

        $request = $this->createUpdateDoctorRequest([
            'id' => $doctor->id,
            'name' => $doctor->name,
            'email' => $doctor->email,
            'phone' => $doctor->phone,
            'gender' => $doctor->gender->value,
            'is_active' => 'on',
            'role' => $this->adminRole->id,
            'password' => 'newpassword123',
        ]);

        $this->repository->update($request);

        $doctor->refresh();
        $this->assertNotEquals($oldPassword, $doctor->password);
    }

    public function test_activate_deactivate_changes_status(): void
    {
        $doctor = $this->createTestDoctor([
            'email' => 'status@test.com',
            'is_active' => 1,
        ]);

        $request = $this->createStatusRequest([
            'id' => $doctor->id,
            'is_active' => 'off',
        ]);

        $this->repository->activateDeActivate($request);

        $doctor->refresh();
        $this->assertEquals(0, $doctor->is_active->value);
    }

    private function createDoctorRequest(array $data): DoctorRequest
    {
        $request = new DoctorRequest;
        $request->merge($data);

        if (isset($data['img'])) {
            $request->files->set('img', $data['img']);
        }

        return $request;
    }

    private function createUpdateDoctorRequest(array $data): UpdateDoctorRequest
    {
        $request = new UpdateDoctorRequest;
        $request->merge($data);

        if (isset($data['img'])) {
            $request->files->set('img', $data['img']);
        }

        return $request;
    }

    private function createStatusRequest(array $data): UpdateStatusAminRequest
    {
        $request = new UpdateStatusAminRequest;
        $request->merge($data);

        return $request;
    }
}
