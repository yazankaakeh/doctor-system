<?php

namespace Modules\Core\App\Http\Middleware;

use Closure as ClosureAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminManagement\Enums\Roles;
use Modules\Doctor\Models\Doctor;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, ClosureAlias $next)
    {
        /**
         * @var Doctor $user
         */
        $user = Auth::user();
        abort_if(
            ! ($user->can($request->route()->getName()) || $user->hasRole(Roles::SUPER_ADMIN->value)),
            Response::HTTP_FORBIDDEN,
            'Sorry!, You dont have the right permission to access this Operation.',
        );

        return $next($request);
    }
}
