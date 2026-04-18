<?php

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
    public function __construct(
        private readonly BookingInterface $repository
    ) {}

    public function index(): View
    {
        return view('booking::patient.booking.wizard');
    }

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
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function myBookings(): View
    {
        $bookings = $this->repository->getForPatient(auth('web')->id());

        return view('booking::patient.booking.my-bookings', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        if ($booking->patient_id !== auth('web')->id()) {
            abort(403);
        }

        $booking->load(['doctor', 'doctor.medicalSpecialty', 'payment']);

        return view('booking::patient.booking.show', compact('booking'));
    }

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
