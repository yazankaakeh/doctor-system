<?php

namespace Modules\AdminManagement\Action\Auditing;

use Illuminate\Support\Facades\Route;
use JetBrains\PhpStorm\ArrayShape;

class RouteName
{
    public static function GetRouteName($routeName)
    {
        $routeNames = self::Routes();

        return $routeNames[$routeName] ?? $routeName;
    }

    public static function Routes(): array
    {
        /* return [
           '' => trans('routes_name.'),
         ];*/
        $newRoutes = [];
        $routes = Route::getRoutes()->getRoutes();
        foreach ($routes as $route) {
            if (! empty($route->getName())
                && isset($route->getAction()['middleware'])
                && $route->methods[0] !== 'GET'
                && in_array('admin-enabled', $route->getAction()['middleware'])
                && str_starts_with($route->uri, 'admin/')
            ) {
                $newRoutes[$route->getName()] = $route->getName();
            }
        }

        return $newRoutes;
    }

    #[ArrayShape(['master-confirm-sorting' => 'mixed'])]
    public static function ImportantRoutesWithGetMethod(): array
    {
        return [
            // put GET request you want to LOG here
        ];
    }
}
