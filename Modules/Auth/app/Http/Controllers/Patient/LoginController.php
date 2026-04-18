<?php

namespace Modules\Auth\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Actions\Patient\LoginAction;
use Modules\Auth\Http\Requests\Patient\LoginRequest;

class LoginController extends Controller
{
    /**
     * Display the patient login form.
     */
    public function showLoginForm(): View
    {
        return view('auth::patient.login');
    }

    /**
     * Handle patient login request.
     */
    public function login(LoginRequest $request, LoginAction $action): RedirectResponse
    {
        $action->handle(
            $request->validated(),
            $request->boolean('remember')
        );

        return redirect()->intended(route('patient.dashboard'))
            ->with('success', __('Welcome back!'));
    }

    /**
     * Handle patient logout request.
     */
    public function logout(): RedirectResponse
    {
        auth('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('patient.login')
            ->with('success', __('You have been logged out successfully.'));
    }
}
