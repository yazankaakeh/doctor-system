<?php

/**
 * -----------------------------------------------------------------------------
 * Api\CalendarController
 * -----------------------------------------------------------------------------
 *
 * JSON endpoints that feed the doctor-facing FullCalendar widget.
 *
 * Frontend contract:
 *   - `GET /api/booking/calendar/availabilities?start=YYYY-MM-DD&end=YYYY-MM-DD`
 *     → Returns the doctor's availability windows/slots in that range.
 *   - `GET /api/booking/calendar/bookings?start=...&end=...&status=<int>`
 *     → Returns the doctor's bookings in that range, optionally filtered
 *        by a BookingStatusEnum value.
 *
 * Both endpoints authenticate through the `doctor` guard and delegate the
 * actual database work to `GetCalendarEventsAction` so the controller stays
 * thin.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Booking\Actions\Calendar\GetCalendarEventsAction;

class CalendarController extends Controller
{
    /**
     * Inject the calendar-events action so we can reuse its query/formatting
     * logic between the two endpoints.
     */
    public function __construct(
        protected GetCalendarEventsAction $calendarAction
    ) {}

    /**
     * Return availability events for the authenticated doctor between the
     * given start and end dates. Used by the doctor calendar view.
     */
    public function availabilities(Request $request): JsonResponse
    {
        // FullCalendar always sends a start/end window; both must be valid dates.
        $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date'],
        ]);

        $doctorId  = auth('doctor')->id();
        $startDate = Carbon::parse($request->start);
        $endDate   = Carbon::parse($request->end);

        // Action is responsible for querying and formatting the events.
        $events = $this->calendarAction->getAvailabilityEvents($doctorId, $startDate, $endDate);

        return response()->json($events);
    }

    /**
     * Return booking events for the authenticated doctor within the given
     * window. An optional `status` filter (BookingStatusEnum integer value)
     * narrows the result to a specific state (pending, confirmed, …).
     */
    public function bookings(Request $request): JsonResponse
    {
        $request->validate([
            'start'  => ['required', 'date'],
            'end'    => ['required', 'date'],
            // Status range 1..5 matches the underlying BookingStatusEnum values.
            'status' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $doctorId  = auth('doctor')->id();
        $startDate = Carbon::parse($request->start);
        $endDate   = Carbon::parse($request->end);
        $status    = $request->status ? (int) $request->status : null;

        $events = $this->calendarAction->getBookingEvents($doctorId, $startDate, $endDate, $status);

        return response()->json($events);
    }
}
