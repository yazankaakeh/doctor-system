@php $page = 'patient-appointments'; @endphp
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('patient::patient.sidebar.my_appointments'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ trans('patient::patient.sidebar.my_appointments') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($appointments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ trans('patient::patient.date') }}</th>
                                                <th>{{ trans('patient::patient.clinic') }}</th>
                                                <th>{{ trans('patient::patient.reason_of_visiting') }}</th>
                                                <th>{{ trans('patient::patient.status') }}</th>
                                                <th>{{ trans('patient::patient.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($appointments as $appointment)
                                                <tr>
                                                    <td>{{ $appointment->id }}</td>
                                                    <td>{{ $appointment->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>{{ $appointment->clinic?->name ?? '-' }}</td>
                                                    <td>{{ Str::limit($appointment->reason_of_visiting, 50) ?? '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $appointment->status?->class() ?? 'secondary' }}">
                                                            {{ $appointment->status?->label() ?? '-' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('patient.appointments.show', $appointment->id) }}"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="ti tabler-eye"></i> {{ trans('patient::patient.view') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $appointments->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="ti tabler-calendar-off icon-48px text-muted mb-3"></i>
                                    <p class="text-muted">{{ trans('patient::patient.no_appointments') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
