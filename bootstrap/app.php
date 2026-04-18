<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Modules\AdminManagement\Http\Middleware\AdminEnabled;
use Modules\AdminManagement\Http\Middleware\AuditLogMiddleware;
use Modules\AdminManagement\Http\Middleware\Authenticate;
use Modules\AdminManagement\Http\Middleware\RedirectIfAuthenticated;
use Modules\Core\App\Http\Middleware\AdminPermissionsMiddleware;
use Modules\Core\app\Http\Middleware\ComingSoon;
use Modules\Core\App\Http\Middleware\SetApiLocale;
use Modules\Core\App\Http\Middleware\SetLocale;
use Modules\Doctor\Http\Middleware\DoctorMenu;
use Modules\Patient\Http\Middleware\PatientMenu;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'doctor' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'admin-enabled' => AdminEnabled::class,
            'audit' => AuditLogMiddleware::class,
            'authorize' => AdminPermissionsMiddleware::class,
            'setLocale' => SetLocale::class,
            'setApiLocale' => SetApiLocale::class,
            'coming_soon' => ComingSoon::class,
            'doctorMenu' => DoctorMenu::class,
            'patientMenu' => PatientMenu::class,
        ]);
        $middleware->append([

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom authentication exception handling for multi-guard system
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            // Redirect to appropriate login based on guard
            $guards = $e->guards();

            if (in_array('doctor', $guards)) {
                return redirect()->guest(route('doctor.login'));
            }

            if (in_array('patient', $guards) || in_array('web', $guards)) {
                return redirect()->guest(route('patient.login'));
            }

            // Default fallback
            return redirect()->guest(route('login'));
        });
    })->create();
