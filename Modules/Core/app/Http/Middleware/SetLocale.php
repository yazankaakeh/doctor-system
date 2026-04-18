<?php

namespace Modules\Core\App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        $locale = Session::get('locale');
        if ($locale == null) {
            $locale = config('app.locale');
        }
        App::setLocale($locale);

        return $next($request);
    }
}
