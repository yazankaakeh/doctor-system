<?php

namespace Modules\AdminManagement\Repository\User;

use App\Enum\Pagination;
use Illuminate\Support\Facades\Hash;
use Modules\AdminManagement\Http\Requests\DoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateDoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateStatusAminRequest;
use Modules\Doctor\Models\Doctor;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\Permission\Models\Role;

class DoctorRepository implements DoctorInterface
{
    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function store(DoctorRequest $request): void
    {
        $doctor = new Doctor;
        $doctor->name = $request->name;
        $doctor->gender = $request->gender;
        $doctor->phone = $request->phone;
        $doctor->age = $request->age;
        $doctor->email = $request->email;
        $doctor->password = Hash::make($request->password);
        $doctor->medical_specialty_id = $request->medicalSpecialtyId;
        $doctor->is_active = $request->is_active == 'on' ? 1 : 0;
        if ($request->file('img')) {
            $doctor->addMedia($request->file('img'))->toMediaCollection('images');
        }
        $doctor->save();
        /** @var Role $role */
        $role = Role::query()->findOrFail($request->role);
        $doctor->assignRole($role);
    }

    public function update(UpdateDoctorRequest $request): void
    {
        /** @var Admin $doctor */
        $doctor = Doctor::query()->updateOrCreate(
            ['id' => $request->id],
            [
                'name' => $request->name,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'email' => $request->email,
                'is_active' => $request->is_active == 'on' ? 1 : 0,
            ],
        );
        if ($request->password !== null) {
            $doctor->password = Hash::make($request->password);
        }
        if ($request->file('img')) {
            $doctor->addMedia($request->file('img'))->toMediaCollection('images');
        }
        $doctor->save();

        // Remove each role from the user
        $doctor->roles()->detach();
        /** @var Role $role */
        $role = Role::query()->findOrFail($request->role);

        $doctor->assignRole($role);
    }

    public function activateDeActivate(UpdateStatusAminRequest $request): void
    {
        Doctor::query()->updateOrCreate(['id' => $request->id],
            [
                'is_active' => $request->is_active == 'on' ? 1 : 0,
            ]);
    }

    public function index(): array
    {
        $users = Doctor::query()->with('roles')
            ->paginate(Pagination::PAG->value);
        $roles = Role::query()->pluck('name', 'id');

        return [
            'users' => $users,
            'roles' => $roles,
        ];
    }
}
