<?php

/**
 * -----------------------------------------------------------------------------
 * Doctor\BookingController
 * -----------------------------------------------------------------------------
 *
 * Controller for the doctor-facing booking screens.
 *
 * Responsibilities:
 *   - Listing upcoming + historical bookings on the doctor dashboard.
 *   - Showing a single booking page (with patient + payment info).
 *   - Transitioning a booking to COMPLETED when the consultation finishes.
 *   - Marking a booking as NO_SHOW when the patient failed to attend.
 *
 * Ownership is enforced on every action to prevent a doctor from viewing or
 * mutating bookings that belong to a different doctor (403 otherwise).
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Http\Controllers\Doctor;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Repository\Booking\BookingInterface;

class BookingController extends Controller
{
    /**
     * Inject the BookingRepository to keep query logic out of the controller.
     */
    public function __construct(
        private readonly BookingInterface $repository
    ) {}

    /**
     * Dashboard listing: upcoming and full list of the doctor's bookings.
     */
    public function index(): View
    {
        $doctorId = auth('doctor')->id();

        // "upcoming" – future appointments, sorted chronologically.
        $bookings = $this->repository->getUpcomingForDoctor($doctorId);
        // "all" – full history used by the secondary tab / archive view.
        $allBookings = $this->repository->getAllForDoctor($doctorId);

        return view('booking::doctor.bookings.index', compact('bookings', 'allBookings'));
    }

    /**
     * Show a single booking in detail. Eager loads relationships the view
     * relies on (patient profile, payment).
     */
    public function show(Booking $booking): View
    {
        // Ownership guard – doctors can only view their own bookings.
        if ($booking->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $booking->load(['patient', 'payment']);

        return view('booking::doctor.bookings.show', compact('booking'));
    }

    /**
     * Transition the booking into COMPLETED once the tele-consultation ends.
     * Only CONFIRMED bookings can be completed.
     */
    public function complete(Booking $booking): RedirectResponse
    {
        // Ownership guard.
        if ($booking->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        // Status guard – cannot complete a booking that is not confirmed.
        if (! $booking->isConfirmed()) {
            return redirect()
                ->back()
                ->with('error', __('booking::booking.cannot_complete'));
        }

        $booking->update(['status' => BookingStatusEnum::COMPLETED]);

        return redirect()
            ->route('doctor.bookings.index')
            ->with('success', __('booking::booking.booking_completed'));
    }

    /**
     * Flag the booking as NO_SHOW when the patient did not attend.
     * Only CONFIRMED bookings can be flagged.
     */
    public function noShow(Booking $booking): RedirectResponse
    {
        // Ownership guard.
        if ($booking->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        // Status guard – cannot no-show a booking that is not confirmed.
        if (! $booking->isConfirmed()) {
            return redirect()
                ->back()
                ->with('error', __('booking::booking.cannot_mark_no_show'));
        }

        $booking->update(['status' => BookingStatusEnum::NO_SHOW]);

        return redirect()
            ->route('doctor.bookings.index')
            ->with('success', __('booking::booking.marked_no_show'));
    }
}
