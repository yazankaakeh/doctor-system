@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::booking.availability'))

@section('vendor-style')
    @livewireStyles
@endsection

@section('vendor-script')
    @livewireScripts
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between pb-2 mb-1">
                            <h5>{{ trans('booking::booking.availability') }}</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addAvailabilityModal"
                                    class="btn btn-primary">
                                <i class="ti tabler-plus icon-base me-1"></i>
                                {{ trans('booking::booking.add_availability') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table datanew">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('booking::booking.date') }}</th>
                                        <th>{{ trans('booking::booking.start_time') }}</th>
                                        <th>{{ trans('booking::booking.end_time') }}</th>
                                        <th>{{ trans('booking::booking.slot_duration') }}</th>
                                        <th>{{ trans('booking::booking.consultation_fee') }}</th>
                                        <th>{{ trans('booking::booking.status') }}</th>
                                        <th>{{ trans('admin.audits.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($availabilities as $availability)
                                        <tr>
                                            <td>{{ $availability->date->format('Y-m-d') }}</td>
                                            <td>{{ $availability->start_time->format('H:i') }}</td>
                                            <td>{{ $availability->end_time->format('H:i') }}</td>
                                            <td>{{ $availability->slot_duration }} min</td>
                                            <td>${{ number_format($availability->consultation_fee, 2) }}</td>
                                            <td>
                                                <span class="badge text-bg-{{ $availability->is_active ? 'success' : 'secondary' }}">
                                                    {{ $availability->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-text-secondary btn-icon rounded-pill"
                                                            type="button" data-bs-toggle="dropdown">
                                                        <i class="ti tabler-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <button type="button" class="dropdown-item editAvailabilityBtn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editAvailabilityModal"
                                                                data-id="{{ $availability->id }}"
                                                                data-date="{{ $availability->date->format('Y-m-d') }}"
                                                                data-start-time="{{ $availability->start_time->format('H:i') }}"
                                                                data-end-time="{{ $availability->end_time->format('H:i') }}"
                                                                data-slot-duration="{{ $availability->slot_duration }}"
                                                                data-consultation-fee="{{ $availability->consultation_fee }}"
                                                                data-is-active="{{ $availability->is_active ? 1 : 0 }}">
                                                            <i class="ti tabler-edit me-1"></i>
                                                            {{ trans('doctor::doctor.edit') }}
                                                        </button>
                                                        <form action="{{ route('doctor.availability.destroy', $availability) }}"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Are you sure?')">
                                                                <i class="ti tabler-trash me-1"></i>
                                                                {{ trans('doctor::doctor.delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                {{ $availabilities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Availability Modal -->
    <div class="modal fade" id="addAvailabilityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('doctor.availability.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('booking::booking.add_availability') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::booking.date') }}</label>
                            <input type="date" name="date" class="form-control" required
                                   min="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">{{ trans('booking::booking.start_time') }}</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">{{ trans('booking::booking.end_time') }}</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::booking.slot_duration') }}</label>
                            <select name="slot_duration" class="form-select">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">60 minutes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::booking.consultation_fee') }}</label>
                            <input type="number" name="consultation_fee" class="form-control" step="0.01"
                                   min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ trans('booking::booking.back') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('doctor::doctor.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Availability Modal -->
    <div class="modal fade" id="editAvailabilityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAvailabilityForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('booking::booking.edit_availability') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::booking.date') }}</label>
                            <input type="date" name="date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">{{ trans('booking::booking.start_time') }}</label>
                                <input type="time" name="start_time" id="edit_start_time" class="form-control"
                                       required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">{{ trans('booking::booking.end_time') }}</label>
                                <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::booking.slot_duration') }}</label>
                            <select name="slot_duration" id="edit_slot_duration" class="form-select">
                                <option value="15">15 minutes</option>
                                <option value="30">30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60">60 minutes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::booking.consultation_fee') }}</label>
                            <input type="number" name="consultation_fee" id="edit_consultation_fee" class="form-control"
                                   step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="edit_is_active" class="form-check-input"
                                       value="1">
                                <label class="form-check-label">{{ trans('booking::booking.is_active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ trans('booking::booking.back') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('doctor::doctor.edit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.querySelectorAll('.editAvailabilityBtn').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = document.getElementById('editAvailabilityForm');
                const baseUrl = '{{ url("doctor/availability") }}';
                form.action = baseUrl + '/' + this.dataset.id;

                document.getElementById('edit_date').value = this.dataset.date;
                document.getElementById('edit_start_time').value = this.dataset.startTime;
                document.getElementById('edit_end_time').value = this.dataset.endTime;
                document.getElementById('edit_slot_duration').value = this.dataset.slotDuration;
                document.getElementById('edit_consultation_fee').value = this.dataset.consultationFee;
                document.getElementById('edit_is_active').checked = this.dataset.isActive === '1';
            });
        });
    </script>
@endsection
