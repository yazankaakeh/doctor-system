<?php

namespace Modules\Auth\Http\Controllers\Patient;

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
    public function show(): View
    {
        return view('auth::patient.verify-email');
    }

    /**
     * Handle the email verification.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('patient.dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('patient.dashboard')
            ->with('success', __('Your email has been verified!'));
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('patient.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', __('Verification link sent!'));
    }
}
