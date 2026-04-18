<?php

namespace Modules\Patient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class PatientMenu
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $arrayData = $this->getMenuData();
        $objectData = json_decode(json_encode($arrayData));
        View::share('menuData', $objectData);

        return $next($request);
    }

    private function getMenuData(): array
    {
        return [
            [
                'menu' => [
                    [
                        'name' => trans('patient::patient.sidebar.dashboard'),
                        'icon' => 'menu-icon tf-icons ti tabler-dashboard',
                        'slug' => route('patient.dashboard'),
                        'url' => route('patient.dashboard'),
                    ],
                    [
                        'name' => trans('booking::booking.book_appointment'),
                        'icon' => 'menu-icon tf-icons ti tabler-calendar-plus',
                        'slug' => route('patient.book.index'),
                        'url' => route('patient.book.index'),
                    ],
                    [
                        'name' => trans('booking::booking.my_bookings'),
                        'icon' => 'menu-icon tf-icons ti tabler-calendar-check',
                        'slug' => route('patient.bookings.index'),
                        'url' => route('patient.bookings.index'),
                    ],
                    [
                        'name' => trans('patient::patient.sidebar.my_appointments'),
                        'icon' => 'menu-icon tf-icons ti tabler-calendar-event',
                        'slug' => route('patient.appointments.index'),
                        'url' => route('patient.appointments.index'),
                    ],
                    [
                        'name' => trans('patient::patient.sidebar.medical_history'),
                        'icon' => 'menu-icon tf-icons ti tabler-history',
                        'slug' => route('patient.medical-history.index'),
                        'url' => route('patient.medical-history.index'),
                    ],
                    [
                        'name' => trans('patient::patient.sidebar.my_profile'),
                        'icon' => 'menu-icon tf-icons ti tabler-user',
                        'slug' => route('patient.profile.index'),
                        'url' => route('patient.profile.index'),
                    ],
                ],
            ],
        ];
    }
}
