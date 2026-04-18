<?php

namespace Modules\Auth\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Actions\Doctor\RegisterAction;
use Modules\Auth\Http\Requests\Doctor\RegisterRequest;
use Modules\Doctor\Models\MedicalSpecialty;

class RegisterController extends Controller
{
    /**
     * Display the doctor registration form.
     */
    public function showRegistrationForm(): View
    {
        $medicalSpecialties = MedicalSpecialty::getMedicalSpecialtySelect2();

        return view('auth::doctor.register', compact('medicalSpecialties'));
    }

    /**
     * Handle doctor registration request.
     */
    public function register(RegisterRequest $request, RegisterAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        // Don't log them in - they need approval first
        return redirect()->route('doctor.login')
            ->with('success', trans('auth::auth.registration_pending'));
    }
}
