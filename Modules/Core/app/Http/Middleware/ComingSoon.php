<?php

namespace Modules\Core\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoon
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('IS_COMING_SOON', 0) == 1) {
            return redirect()->route('landing.coming_soon');
        }

        return $next($request);
    }
}
