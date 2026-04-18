<?php

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
    public function __construct(
        protected RecurringScheduleInterface $repository
    ) {}

    public function index(): View
    {
        $doctorId = auth('doctor')->id();
        $schedules = $this->repository->getByDoctor($doctorId);

        return view('booking::doctor.recurring-schedules.index', compact('schedules'));
    }

    public function store(
        StoreRecurringScheduleRequest $request,
        CreateRecurringScheduleAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', __('booking::recurring.messages.created'));
    }

    public function update(
        UpdateRecurringScheduleRequest $request,
        int $id,
        UpdateRecurringScheduleAction $action
    ): RedirectResponse {
        $schedule = $this->repository->find($id);

        if ($schedule->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id, $request->validated());

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', __('booking::recurring.messages.updated'));
    }

    public function destroy(int $id, DeleteRecurringScheduleAction $action): RedirectResponse
    {
        $schedule = $this->repository->find($id);

        if ($schedule->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id);

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', __('booking::recurring.messages.deleted'));
    }

    public function toggleStatus(int $id, UpdateRecurringScheduleAction $action): RedirectResponse
    {
        $schedule = $this->repository->find($id);

        if ($schedule->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id, ['is_active' => ! $schedule->is_active]);

        $message = $schedule->is_active
            ? __('booking::recurring.messages.deactivated')
            : __('booking::recurring.messages.activated');

        return redirect()
            ->route('doctor.recurring-schedules.index')
            ->with('success', $message);
    }

    public function generate(
        GenerateAvailabilitiesRequest $request,
        GenerateAvailabilitiesFromScheduleAction $action
    ): RedirectResponse {
        $doctorId = auth('doctor')->id();
        $startDate = Carbon::parse($request->validated('start_date'));
        $endDate = Carbon::parse($request->validated('end_date'));

        $result = $action->handle($doctorId, $startDate, $endDate);

        return redirect()
            ->route('doctor.calendar.availability')
            ->with('success', __('booking::recurring.messages.generated', [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ]));
    }
}
