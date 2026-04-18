<?php

namespace Modules\Booking\Http\Controllers\Doctor;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Repository\Booking\BookingInterface;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingInterface $repository
    ) {}

    public function index(): View
    {
        $doctorId = auth('doctor')->id();
        $bookings = $this->repository->getUpcomingForDoctor($doctorId);
        $allBookings = $this->repository->getAllForDoctor($doctorId);

        return view('booking::doctor.bookings.index', compact('bookings', 'allBookings'));
    }

    public function show(Booking $booking): View
    {
        if ($booking->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $booking->load(['patient', 'payment']);

        return view('booking::doctor.bookings.show', compact('booking'));
    }

    public function complete(Booking $booking): RedirectResponse
    {
        if ($booking->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        if (!$booking->isConfirmed()) {
            return redirect()
                ->back()
                ->with('error', __('booking::booking.cannot_complete'));
        }

        $booking->update(['status' => BookingStatusEnum::COMPLETED]);

        return redirect()
            ->route('doctor.bookings.index')
            ->with('success', __('booking::booking.booking_completed'));
    }

    public function noShow(Booking $booking): RedirectResponse
    {
        if ($booking->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        if (!$booking->isConfirmed()) {
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
