<?php

namespace Modules\AdminManagement\Http\Middleware;

use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\AdminManagement\Action\Auditing\RouteName;
use Modules\AdminManagement\Models\AuditLog;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $currentRouteName = Route::currentRouteName();
        $exceptRoutes = RouteName::ImportantRoutesWithGetMethod();

        if ($request->method() == 'GET' &&
            ! array_key_exists($currentRouteName, $exceptRoutes)) {
            return $next($request);
        }

        if (in_array($currentRouteName, [
            'admin_save_push_token',
            'search_student_dashboard',
        ])) {
            return $next($request);
        }

        if (! Auth::check()) {
            return $next($request);
        }

        /* @var User $user */
        $user = Auth::user();
        if ($user->email) {
            try {
                // Create audit log using morph relationship
                $auditLogData = [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'payload' => $request->except(['img', 'password', 'password_confirmation', '_method']),
                    'ip' => $request->ip(),
                    'route_name' => $currentRouteName,
                ];

                // Use the user's createAuditLog method if it has the trait
                if (method_exists($user, 'createAuditLog') && is_callable([$user, 'createAuditLog'])) {
                    /** @var \Modules\AdminManagement\Traits\AuditLogTrait $user */
                    $user->createAuditLog($auditLogData);
                } else {
                    // Fallback to direct creation with morph fields
                    AuditLog::query()->create([
                        'auditable_type' => get_class($user),
                        'auditable_id' => $user->id,
                        ...$auditLogData,
                    ]);
                }
            } catch (Exception $e) {
                // Log the error instead of dd() for production
                Log::error('AuditLog creation failed: '.$e->getMessage(), [
                    'user_id' => $user->id,
                    'route' => $currentRouteName,
                    'url' => $request->url(),
                ]);
            }
        }

        return $next($request);
    }
}
