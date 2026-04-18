@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('booking::recurring.exceptions_title'))

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
                            <h5>{{ trans('booking::recurring.exceptions_title') }}</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addExceptionModal"
                                    class="btn btn-primary">
                                <i class="ti tabler-plus icon-base me-1"></i>
                                {{ trans('booking::recurring.add_exception') }}
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
                                <table class="table datanew">
                                    <thead>
                                    <tr>
                                        <th>{{ trans('booking::recurring.fields.exception_date') }}</th>
                                        <th>{{ trans('booking::recurring.fields.exception_type') }}</th>
                                        <th>{{ trans('booking::recurring.fields.reason') }}</th>
                                        <th>{{ trans('booking::recurring.alternate_times') }}</th>
                                        <th>{{ trans('admin.audits.action') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($exceptions as $exception)
                                        <tr>
                                            <td>{{ $exception->exception_date->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge text-bg-{{ $exception->isSkip() ? 'danger' : 'warning' }}">
                                                    {{ trans('booking::recurring.exception_types.' . $exception->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $exception->reason ?? '-' }}</td>
                                            <td>{{ $exception->formatted_alternate_time_range ?? '-' }}</td>
                                            <td>
                                                <form action="{{ route('doctor.schedule-exceptions.destroy', $exception) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('{{ trans('booking::recurring.confirm_delete_exception') }}')">
                                                        <i class="ti tabler-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Exception Modal -->
    <div class="modal fade" id="addExceptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('doctor.schedule-exceptions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('booking::recurring.add_exception') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::recurring.fields.recurring_schedule') }}</label>
                            <select name="recurring_schedule_id" class="form-select">
                                <option value="">{{ trans('booking::recurring.all_schedules') }}</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}">
                                        {{ $schedule->day_name }} ({{ $schedule->formatted_time_range }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::recurring.fields.exception_date') }}</label>
                            <input type="date" name="exception_date" class="form-control" required
                                   min="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::recurring.fields.exception_type') }}</label>
                            <select name="type" id="exceptionType" class="form-select" required>
                                <option value="skip">{{ trans('booking::recurring.exception_types.skip') }}</option>
                                <option value="modified">{{ trans('booking::recurring.exception_types.modified') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('booking::recurring.fields.reason') }}</label>
                            <textarea name="reason" class="form-control" rows="2"
                                      placeholder="{{ trans('booking::recurring.reason_placeholder') }}"></textarea>
                        </div>
                        <div id="alternateTimesSection" class="d-none">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.alternate_start_time') }}</label>
                                    <input type="time" name="alternate_start_time" class="form-control">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">{{ trans('booking::recurring.fields.alternate_end_time') }}</label>
                                    <input type="time" name="alternate_end_time" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ trans('booking::booking.back') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ trans('doctor::doctor.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.getElementById('exceptionType').addEventListener('change', function() {
            const section = document.getElementById('alternateTimesSection');
            if (this.value === 'modified') {
                section.classList.remove('d-none');
            } else {
                section.classList.add('d-none');
            }
        });
    </script>
@endsection
