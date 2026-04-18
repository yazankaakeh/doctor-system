<?php

namespace Modules\Auth\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Actions\Patient\RegisterAction;
use Modules\Auth\Http\Requests\Patient\RegisterRequest;

class RegisterController extends Controller
{
    /**
     * Display the patient registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth::patient.register');
    }

    /**
     * Handle patient registration request.
     */
    public function register(RegisterRequest $request, RegisterAction $action): RedirectResponse
    {
        $patient = $action->handle($request->validated());

        return redirect()->route('patient.dashboard')
            ->with('success', __('Your account has been created successfully! Please verify your email.'));
    }
}
