<?php

namespace Modules\AdminManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminManagement\app\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (Auth::guard('doctor')->attempt(['email' => $request->email, 'password' => $request->password],
            $request->get('remember'))) {
            return auth()->guard('doctor')->attempt(['email' => $request->email, 'password' => $request->password],
                $request->get('remember'))
                ? redirect()->intended(route('doctor.dashboard.index'))
                : back()->withInput($request->only('email', 'remember'));
        }

        return back()->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::guard('doctor')->logout();

        return redirect()->route('admin.login');
    }
}
