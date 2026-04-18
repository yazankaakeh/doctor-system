<?php

namespace Modules\Booking\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Booking\Actions\Calendar\GetCalendarEventsAction;

class CalendarController extends Controller
{
    public function __construct(
        protected GetCalendarEventsAction $calendarAction
    ) {}

    public function availabilities(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $doctorId = auth('doctor')->id();
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $events = $this->calendarAction->getAvailabilityEvents($doctorId, $startDate, $endDate);

        return response()->json($events);
    }

    public function bookings(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'status' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $doctorId = auth('doctor')->id();
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);
        $status = $request->status ? (int) $request->status : null;

        $events = $this->calendarAction->getBookingEvents($doctorId, $startDate, $endDate, $status);

        return response()->json($events);
    }
}
