<?php

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
    public function __construct(
        protected ScheduleExceptionInterface $repository,
        protected RecurringScheduleInterface $scheduleRepository
    ) {}

    public function index(): View
    {
        $doctorId = auth('doctor')->id();
        $exceptions = $this->repository->getByDoctor($doctorId);
        $schedules = $this->scheduleRepository->getActiveByDoctor($doctorId);

        return view('booking::doctor.schedule-exceptions.index', compact('exceptions', 'schedules'));
    }

    public function store(
        StoreScheduleExceptionRequest $request,
        CreateScheduleExceptionAction $action
    ): RedirectResponse {
        $action->handle($request->validated());

        return redirect()
            ->route('doctor.schedule-exceptions.index')
            ->with('success', __('booking::recurring.messages.exception_created'));
    }

    public function destroy(int $id, DeleteScheduleExceptionAction $action): RedirectResponse
    {
        $exception = $this->repository->find($id);

        if ($exception->doctor_id !== auth('doctor')->id()) {
            abort(403);
        }

        $action->handle($id);

        return redirect()
            ->route('doctor.schedule-exceptions.index')
            ->with('success', __('booking::recurring.messages.exception_deleted'));
    }
}
