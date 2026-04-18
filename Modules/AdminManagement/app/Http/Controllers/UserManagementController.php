<?php

namespace Modules\AdminManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\AdminManagement\Http\Requests\DoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateDoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateStatusAminRequest;
use Modules\AdminManagement\Repository\User\DoctorInterface;
use Modules\AdminManagement\Repository\User\DoctorRepository;
use Modules\Doctor\Models\MedicalSpecialty;

class UserManagementController extends Controller
{
    public function __construct(public DoctorInterface $userInterface) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        /** @var DoctorRepository $userRepo */
        $userRepo = $this->userInterface->index();
        $users = $userRepo['users'];
        $roles = $userRepo['roles'];
        $medicalSpecialty = MedicalSpecialty::getMedicalSpecialtySelect2();

        return view('adminmanagement::users.index', compact('users', 'roles', 'medicalSpecialty'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorRequest $request): RedirectResponse
    {
        $this->userInterface->update($request);

        return redirect()->route('admin.user_management.index')->with(
            'success',
            trans('mps::mps.success.updatedSuccess'),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorRequest $request): RedirectResponse
    {
        $this->userInterface->store($request);

        return redirect()->route('admin.user_management.index')->with(
            'success',
            trans('mps::mps.success.createdSuccess'),
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function status(UpdateStatusAminRequest $request): RedirectResponse
    {
        $this->userInterface->activateDeActivate($request);

        return redirect()->route('admin.user_management.index')->with(
            'success',
            trans('mps::mps.success.updateStatusSuccess'),
        );
    }
}
