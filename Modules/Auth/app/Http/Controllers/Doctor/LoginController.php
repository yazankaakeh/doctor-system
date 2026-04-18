<?php

namespace Modules\Auth\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Actions\Doctor\LoginAction;
use Modules\Auth\Http\Requests\Doctor\LoginRequest;

class LoginController extends Controller
{
    /**
     * Display the doctor login form.
     */
    public function showLoginForm(): View
    {
        return view('auth::doctor.login');
    }

    /**
     * Handle doctor login request.
     */
    public function login(LoginRequest $request, LoginAction $action): RedirectResponse
    {
        $action->handle(
            $request->validated(),
            $request->boolean('remember')
        );

        return redirect()->intended(route('doctor.dashboard'))
            ->with('success', __('Welcome back!'));
    }

    /**
     * Handle doctor logout request.
     */
    public function logout(): RedirectResponse
    {
        auth('doctor')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('doctor.login')
            ->with('success', __('You have been logged out successfully.'));
    }
}
