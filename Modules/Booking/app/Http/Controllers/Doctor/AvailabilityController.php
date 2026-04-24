<?php

/**
 * -----------------------------------------------------------------------------
 * Doctor\AvailabilityController
 * -----------------------------------------------------------------------------
 *
 * CRUD controller used by the logged-in doctor to manage their manual
 * availability slots (one-off entries that do not belong to a recurring
 * template).
 *
 * Following the module's thin-controller convention, all business logic is
 * delegated to Action classes:
 *   - CreateAvailabilityAction  → inserts a new window
 *   - UpdateAvailabilityAction  → updates an existing window
 *   - DeleteAvailabilityAction  → removes a window (safely)
 *
 * Data access is performed through AvailabilityInterface (a repository) to
 * keep query logic testable and swappable.
 * -----------------------------------------------------------------------------
 */

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
    /**
     * Inject the repository so query logic stays out of the controller.
     */
    public function __construct(
        private readonly AvailabilityInterface $repository
    ) {}

    /**
     * Show the list of availabilities belonging to the authenticated doctor.
     */
    public function index(): View
    {
        // Fetch only the current doctor's availabilities.
        $availabilities = $this->repository->getForDoctor(auth('doctor')->id());

        return view('booking::doctor.availability.index', compact('availabilities'));
    }

    /**
     * Persist a newly created availability window.
     *
     * - Validation happens in StoreAvailabilityRequest.
     * - Persistence happens in CreateAvailabilityAction.
     */
    public function store(
        StoreAvailabilityRequest $request,
        CreateAvailabilityAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return redirect()
            ->route('doctor.availability.index')
            ->with('success', __('booking::booking.availability_created'));
    }

    /**
     * Update an existing availability window.
     *
     * Route-model binding (`DoctorAvailability $availability`) fetches the row;
     * authorisation to edit that row is enforced inside UpdateAvailabilityAction.
     */
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

    /**
     * Delete an availability window.
     *
     * Extra guard: make sure the availability actually belongs to the
     * authenticated doctor before handing it to the delete action.
     */
    public function destroy(
        DoctorAvailability $availability,
        DeleteAvailabilityAction $action
    ): RedirectResponse {
        // Prevent a doctor from deleting another doctor's row by forging an ID.
        if ($availability->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($availability->id);

        return redirect()
            ->route('doctor.availability.index')
            ->with('success', __('booking::booking.availability_deleted'));
    }
}
