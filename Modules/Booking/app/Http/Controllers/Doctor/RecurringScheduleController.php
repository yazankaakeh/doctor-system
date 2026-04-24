<?php

/**
 * -----------------------------------------------------------------------------
 * Doctor\RecurringScheduleController
 * -----------------------------------------------------------------------------
 *
 * Controller for managing a doctor's weekly recurring availability templates
 * (DoctorRecurringSchedule). The UI exposes:
 *
 *   - index()        → List all recurring templates for the doctor.
 *   - store()        → Create a new template.
 *   - update()       → Edit an existing template.
 *   - destroy()      → Soft-delete a template.
 *   - toggleStatus() → Quickly flip is_active on/off from the list.
 *   - generate()     → Spawn concrete DoctorAvailability rows for a date
 *                       range from the doctor's active templates (and any
 *                       exceptions).
 *
 * All persistence logic lives in dedicated Action classes so the controller
 * stays thin. Ownership (doctor_id === auth()->id()) is enforced on every
 * mutating endpoint.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Booking\Actions\RecurringSchedule\CreateRecurringScheduleAction;
use Modules\Booking\Actions\RecurringSchedule\DeleteRecurringScheduleAction;
use Modules\Booking\Actions\RecurringSchedule\GenerateAvailabilitiesFromScheduleAction;
use Modules\Booking\Actions\RecurringSchedule\UpdateRecurringScheduleAction;
use Modules\Booking\Http\Requests\GenerateAvailabilitiesRequest;
use Modules\Booking\Http\Requests\RecurringSchedule\StoreRecurringScheduleRequest;
use Modules\Booking\Http\Requests\RecurringSchedule\UpdateRecurringScheduleRequest;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;

class RecurringScheduleController extends Controller
{
    /**
     * Inject the recurring-schedule repository for read queries.
     */
    public function __construct(
        protected RecurringScheduleInterface $repository
    ) {}

    /**
     * List recurring templates belonging to the authenticated doctor.
     */
    public function index(): View
    {
        $doctorId = auth('doctor')->id();
        $schedules = $this->repository->getByDoctor($doctorId);

        return view('booking::doctor.recurring-schedules.index', compact('schedules'));
    }

    /**
     * Persist a new recurring template. The Action performs validation of
     * business invariants (no overlap, positive duration, etc.) beyond the
     * field-level rules in the FormRequest.
     */
    public function store(
        StoreRecurringScheduleRequest $request,
        CreateRecurringScheduleAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', __('booking::recurring.messages.created'));
    }

    /**
     * Update an existing template.
     * Ownership is verified before calling the Action.
     */
    public function update(
        UpdateRecurringScheduleRequest $request,
        int $id,
        UpdateRecurringScheduleAction $action
    ): RedirectResponse {
        $schedule = $this->repository->find($id);

        // Prevent a doctor from editing a template that belongs to someone else.
        if ($schedule->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id, $request->validated());

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', __('booking::recurring.messages.updated'));
    }

    /**
     * Soft-delete a recurring template.
     */
    public function destroy(int $id, DeleteRecurringScheduleAction $action): RedirectResponse
    {
        $schedule = $this->repository->find($id);

        // Ownership guard.
        if ($schedule->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id);

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', __('booking::recurring.messages.deleted'));
    }

    /**
     * Flip `is_active` on the template. Used by the "Enable / Disable" button
     * in the list view — saves a round-trip to the edit screen.
     */
    public function toggleStatus(int $id, UpdateRecurringScheduleAction $action): RedirectResponse
    {
        $schedule = $this->repository->find($id);

        // Ownership guard.
        if ($schedule->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        // Reuse the update action so side-effects (cache bust, events…) fire.
        $action->handle($id, ['is_active' => ! $schedule->is_active]);

        // Note: `is_active` on the fetched model is still the *old* value here,
        // so we invert it again when picking the flash message.
        $message = $schedule->is_active
            ? __('booking::recurring.messages.deactivated')
            : __('booking::recurring.messages.activated');

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', $message);
    }

    /**
     * Generate concrete DoctorAvailability rows from the doctor's active
     * recurring templates for the requested date range. Skips days with a
     * matching DoctorScheduleException (type=skip) and respects modified
     * exceptions.
     */
    public function generate(
        GenerateAvailabilitiesRequest $request,
        GenerateAvailabilitiesFromScheduleAction $action
    ): RedirectResponse {
        $doctorId = auth('doctor')->id();
        $startDate = Carbon::parse($request->validated('start_date'));
        $endDate = Carbon::parse($request->validated('end_date'));

        // The action returns counts of `created` vs `skipped` rows so we can
        // show the doctor a meaningful success message.
        $result = $action->handle($doctorId, $startDate, $endDate);

        return redirect()
            ->route('doctor.calendar.availability')
            ->with('success', __('booking::recurring.messages.generated', [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ]));
    }
}
