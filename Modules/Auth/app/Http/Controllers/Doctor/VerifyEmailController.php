<?php

namespace Modules\Auth\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(): View
    {
        return view('auth::doctor.verify-email');
    }

    /**
     * Handle the email verification.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user('doctor')->hasVerifiedEmail()) {
            return redirect()->route('doctor.dashboard');
        }

        if ($request->user('doctor')->markEmailAsVerified()) {
            event(new Verified($request->user('doctor')));
        }

        return redirect()->route('doctor.dashboard')
            ->with('success', __('Your email has been verified!'));
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user('doctor')->hasVerifiedEmail()) {
            return redirect()->route('doctor.dashboard');
        }

        $request->user('doctor')->sendEmailVerificationNotification();

        return back()->with('success', __('Verification link sent!'));
    }
}
