<?php

namespace Modules\Doctor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Doctor\Actions\Profile\UpdateProfileAction;
use Modules\Doctor\Http\Requests\UpdateProfileRequest;
use Modules\Doctor\Models\MedicalSpecialty;

class ProfileController extends Controller
{
    /**
     * Display doctor profile.
     */
    public function index(): View
    {
        $doctor = auth('doctor')->user();
        $specialties = MedicalSpecialty::query()
            ->where('is_active', 1)
            ->get();

        return view('doctor::doctor.profile.index', compact('doctor', 'specialties'));
    }

    /**
     * Update doctor profile.
     */
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        $doctor = auth('doctor')->user();

        $action->handle($doctor, $request->validated(), $request->file('avatar'));

        return redirect()->route('doctor.profile.index')
            ->with('success', trans('doctor::doctor.profile.profile_updated'));
    }
}
