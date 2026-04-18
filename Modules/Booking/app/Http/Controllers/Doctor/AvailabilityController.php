<?php

namespace Modules\Booking\Http\Controllers\Doctor;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Booking\Actions\Availability\CreateAvailabilityAction;
use Modules\Booking\Actions\Availability\DeleteAvailabilityAction;
use Modules\Booking\Actions\Availability\UpdateAvailabilityAction;
use Modules\Booking\Http\Requests\Availability\StoreAvailabilityRequest;
use Modules\Booking\Http\Requests\Availability\UpdateAvailabilityRequest;
use Modules\Booking\Models\DoctorAvailability;
use Modules\Booking\Repository\Availability\AvailabilityInterface;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityInterface $repository
    ) {}

    public function index(): View
    {
        $availabilities = $this->repository->getForDoctor(auth('doctor')->id());

        return view('booking::doctor.availability.index', compact('availabilities'));
    }

    public function store(
        StoreAvailabilityRequest $request,
        CreateAvailabilityAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return redirect()
            ->route('doctor.availability.index')
            ->with('success', __('booking::booking.availability_created'));
    }

    public function update(
        DoctorAvailability $availability,
        UpdateAvailabilityRequest $request,
        UpdateAvailabilityAction $action
    ): RedirectResponse {
        $action->handle($availability->id, $request->validated());

        return redirect()
            ->route('doctor.availability.index')
            ->with('success', __('booking::booking.availability_updated'));
    }

    public function destroy(
        DoctorAvailability $availability,
        DeleteAvailabilityAction $action
    ): RedirectResponse {
        if ($availability->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($availability->id);

        return redirect()
            ->route('doctor.availability.index')
            ->with('success', __('booking::booking.availability_deleted'));
    }
}
