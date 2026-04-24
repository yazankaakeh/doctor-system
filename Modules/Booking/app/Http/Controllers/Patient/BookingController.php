<?php

/**
 * -----------------------------------------------------------------------------
 * Patient\BookingController
 * -----------------------------------------------------------------------------
 *
 * Controller for the patient-facing booking flows:
 *
 *   - index()      → Render the booking wizard (choose doctor → slot → pay).
 *   - store()      → Persist a new booking when the wizard is submitted.
 *   - myBookings() → Show the patient's bookings dashboard.
 *   - show()       → View a single booking (with doctor & payment info).
 *   - cancel()     → Cancel an existing booking with an optional reason.
 *
 * Business logic lives in CreateBookingAction / CancelBookingAction so we can
 * reuse it from other surfaces (e.g. Livewire wizard, admin panel) and unit
 * test it without spinning up HTTP.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Http\Controllers\Patient;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Actions\Booking\CancelBookingAction;
use Modules\Booking\Actions\Booking\CreateBookingAction;
use Modules\Booking\Http\Requests\Booking\CancelBookingRequest;
use Modules\Booking\Http\Requests\Booking\CreateBookingRequest;
use Modules\Booking\Models\Booking;
use Modules\Booking\Repository\Booking\BookingInterface;

class BookingController extends Controller
{
    /**
     * Inject the BookingRepository to fetch patient bookings.
     */
    public function __construct(
        private readonly BookingInterface $repository
    ) {}

    /**
     * Show the multi-step booking wizard (powered by Livewire).
     */
    public function index(): View
    {
        return view('booking::patient.booking.wizard');
    }

    /**
     * Create a new booking.
     *
     * Any business-logic failure inside the action (e.g. slot taken, doctor
     * offline, payment issue) is surfaced as a flash error back to the form.
     */
    public function store(
        CreateBookingRequest $request,
        CreateBookingAction $action
    ): RedirectResponse {
        try {
            $booking = $action->handle($request->validated());

            return redirect()
                ->route('patient.bookings.show', $booking)
                ->with('success', __('booking::booking.booking_created'));
        } catch (\Exception $e) {
            // Return the user-friendly exception message as a flash error.
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Dashboard listing: show every booking belonging to the logged-in patient.
     */
    public function myBookings(): View
    {
        $bookings = $this->repository->getForPatient(auth('web')->id());

        return view('booking::patient.booking.my-bookings', compact('bookings'));
    }

    /**
     * Show one booking in detail with doctor + specialty + payment eager loaded.
     */
    public function show(Booking $booking): View
    {
        // Ownership guard – patients can only view their own bookings.
        if ($booking->patient_id !== auth('web')->id()) {
            abort(403);
        }

        $booking->load(['doctor', 'doctor.medicalSpecialty', 'payment']);

        return view('booking::patient.booking.show', compact('booking'));
    }

    /**
     * Cancel a booking. The CancelBookingAction also takes care of:
     *   - status transitions,
     *   - refund handling (through the Payment module),
     *   - firing the BookingCancelledNotification.
     */
    public function cancel(
        Booking $booking,
        CancelBookingRequest $request,
        CancelBookingAction $action
    ): RedirectResponse {
        try {
            $action->handle($booking->id, $request->validated()['cancellation_reason'] ?? null);

            return redirect()
                ->route('patient.bookings.index')
                ->with('success', __('booking::booking.booking_cancelled'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
