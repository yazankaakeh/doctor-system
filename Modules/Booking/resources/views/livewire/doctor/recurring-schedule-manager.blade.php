{{--
    Livewire view: doctor/recurring-schedule-manager.
    CRUD UI for DoctorRecurringSchedule records. Exposes:
      - List of templates with effective dates + status
      - Create/edit modals backed by Livewire properties
      - "Generate availabilities" action that spawns DoctorAvailability rows
        from active templates for a future date range.
--}}
<div>
    <div class="card">
        {{-- Card header: module title + "Add schedule" button. --}}
        <div class="card-header d-flex justify-content-between pb-2 mb-1">
            <h5>{{ trans('booking::recurring.title') }}</h5>
            <button type="button" wire:click="openCreateModal" class="btn btn-primary">
                <i class="ti tabler-plus icon-base me-1"></i>
                {{ trans('booking::recurring.add_schedule') }}
            </button>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>{{ trans('booking::recurring.fields.day_of_week') }}</th>
                        <th>{{ trans('booking::recurring.time_range') }}</th>
                        <th>{{ trans('booking::recurring.fields.slot_duration') }}</th>
                        <th>{{ trans('booking::recurring.fields.consultation_fee') }}</th>
                        <th>{{ trans('booking::recurring.fields.effective_from') }}</th>
                        <th>{{ trans('booking::recurring.fields.effective_until') }}</th>
                        <th>{{ trans('booking::booking.status') }}</th>
                        <th>{{ trans('admin.audits.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td>{{ $this->days[$schedule['day_of_week']] }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}
                            </td>
                            <td>{{ $schedule['slot_duration'] }} {{ trans('booking::recurring.minutes') }}</td>
                            <td>${{ number_format($schedule['consultation_fee'], 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($schedule['effective_from'])->format('Y-m-d') }}</td>
                            <td>{{ $schedule['effective_until'] ? \Carbon\Carbon::parse($schedule['effective_until'])->format('Y-m-d') : '-' }}</td>
                            <td>
                                <button wire:click="toggleStatus({{ $schedule['id'] }})"
                                        class="btn btn-sm btn-{{ $schedule['is_active'] ? 'success' : 'secondary' }}">
                                    {{ $schedule['is_active'] ? trans('booking::recurring.active') : trans('booking::recurring.inactive') }}
                                </button>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" wire:click="openEditModal({{ $schedule['id'] }})"
                                            class="btn btn-sm btn-outline-primary">
                                        <i class="ti tabler-edit"></i>
                                    </button>
                                    <button type="button" wire:click="confirmDelete({{ $schedule['id'] }})"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="ti tabler-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                {{ trans('booking::recurring.no_schedules') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $editingScheduleId ? trans('booking::recurring.edit_schedule') : trans('booking::recurring.add_schedule') }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ trans('booking::recurring.fields.day_of_week') }}</label>
                                <select wire:model="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror">
                                    <option value="">{{ trans('booking::recurring.select_day') }}</option>
                                    @foreach($this->days as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('day_of_week') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.start_time') }}</label>
                                    <input type="time" wire:model="start_time"
                                           class="form-control @error('start_time') is-invalid @enderror">
                                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.end_time') }}</label>
                                    <input type="time" wire:model="end_time"
                                           class="form-control @error('end_time') is-invalid @enderror">
                                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ trans('booking::recurring.fields.slot_duration') }}</label>
                                <select wire:model="slot_duration" class="form-select @error('slot_duration') is-invalid @enderror">
                                    <option value="15">15 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="20">20 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="30">30 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="45">45 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="60">60 {{ trans('booking::recurring.minutes') }}</option>
                                    <option value="90">90 {{ trans('booking::recurring.minutes') }}</option>
                                </select>
                                @error('slot_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ trans('booking::recurring.fields.consultation_fee') }}</label>
                                <input type="number" wire:model="consultation_fee" step="0.01" min="0"
                                       class="form-control @error('consultation_fee') is-invalid @enderror">
                                @error('consultation_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.effective_from') }}</label>
                                    <input type="date" wire:model="effective_from"
                                           class="form-control @error('effective_from') is-invalid @enderror"
                                           min="{{ now()->format('Y-m-d') }}">
                                    @error('effective_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.effective_until') }}</label>
                                    <input type="date" wire:model="effective_until"
                                           class="form-control @error('effective_until') is-invalid @enderror">
                                    @error('effective_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">{{ trans('booking::recurring.leave_empty_for_indefinite') }}</small>
                                </div>
                            </div>

                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                                <label class="form-check-label" for="is_active">
                                    {{ trans('booking::recurring.is_active') }}
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                {{ trans('booking::booking.back') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $editingScheduleId ? trans('doctor::doctor.edit') : trans('doctor::doctor.create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('booking::recurring.confirm_delete') }}</h5>
                        <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ trans('booking::recurring.delete_warning') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            {{ trans('booking::booking.back') }}
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            {{ trans('doctor::doctor.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
