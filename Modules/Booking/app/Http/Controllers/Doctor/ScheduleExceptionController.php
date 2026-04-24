<?php

/**
 * -----------------------------------------------------------------------------
 * Doctor\ScheduleExceptionController
 * -----------------------------------------------------------------------------
 *
 * Lets a doctor register exceptions to their weekly recurring schedule:
 *   - "Skip" an entire day (e.g. vacation, holiday).
 *   - "Modify" a day so it uses alternate start/end times.
 *
 * The page lists existing exceptions and the active recurring templates they
 * can attach to. Create/delete logic lives in dedicated Action classes.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Booking\Actions\ScheduleException\CreateScheduleExceptionAction;
use Modules\Booking\Actions\ScheduleException\DeleteScheduleExceptionAction;
use Modules\Booking\Http\Requests\ScheduleException\StoreScheduleExceptionRequest;
use Modules\Booking\Repository\RecurringSchedule\RecurringScheduleInterface;
use Modules\Booking\Repository\ScheduleException\ScheduleExceptionInterface;

class ScheduleExceptionController extends Controller
{
    /**
     * Repositories are injected so the controller stays free of SQL logic.
     *
     * - $repository          → exceptions for the doctor
     * - $scheduleRepository  → active recurring templates (used to populate
     *                           the "which schedule does this exception
     *                           apply to?" select on the form).
     */
    public function __construct(
        protected ScheduleExceptionInterface $repository,
        protected RecurringScheduleInterface $scheduleRepository
    ) {}

    /**
     * Show the list of exceptions + the form to create a new one.
     */
    public function index(): View
    {
        $doctorId = auth('doctor')->id();
        $exceptions = $this->repository->getByDoctor($doctorId);
        $schedules = $this->scheduleRepository->getActiveByDoctor($doctorId);

        return view('booking::doctor.schedule-exceptions.index', compact('exceptions', 'schedules'));
    }

    /**
     * Persist a new schedule exception.
     * Validation lives in StoreScheduleExceptionRequest; the Action handles
     * ownership, conflict checks, and persistence.
     */
    public function store(
        StoreScheduleExceptionRequest $request,
        CreateScheduleExceptionAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return redirect()
            ->route('doctor.schedule-exceptions.index')
            ->with('success', __('booking::recurring.messages.exception_created'));
    }

    /**
     * Remove an existing exception. Ensures the exception belongs to the
     * current doctor before delegating to the delete action.
     */
    public function destroy(int $id, DeleteScheduleExceptionAction $action): RedirectResponse
    {
        $exception = $this->repository->find($id);

        // Ownership guard.
        if ($exception->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id);

        return redirect()
            ->route('doctor.schedule-exceptions.index')
            ->with('success', __('booking::recurring.messages.exception_deleted'));
    }
}
