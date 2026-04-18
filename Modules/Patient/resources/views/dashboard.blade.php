@php $page = 'patient-dashboard'; @endphp
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('patient::patient.sidebar.dashboard'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'],'build/modules/theme')
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'],'build/modules/theme')
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            {{-- Welcome Section --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h4 class="text-white mb-1">{{ trans('patient::patient.welcome', ['name' => $patient->name]) }}</h4>
                            <p class="mb-0">{{ trans('patient::patient.dashboard_subtitle') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="row mb-5">
                <div class="col-lg-4 col-md-4 col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-primary mb-3 rounded">
                                <i class="icon-base ti tabler-calendar-event icon-28px"></i>
                            </div>
                            <h5 class="card-title mb-1">{{ trans('patient::patient.total_appointments') }}</h5>
                            <p class="text-heading mb-3 mt-1 fs-4 fw-bold">{{ $data['totalAppointments'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-success mb-3 rounded">
                                <i class="icon-base ti tabler-clock icon-28px"></i>
                            </div>
                            <h5 class="card-title mb-1">{{ trans('patient::patient.upcoming_appointments') }}</h5>
                            <p class="text-heading mb-3 mt-1 fs-4 fw-bold">{{ $data['upcomingAppointments'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="badge p-2 bg-label-info mb-3 rounded">
                                <i class="icon-base ti tabler-building-hospital icon-28px"></i>
                            </div>
                            <h5 class="card-title mb-1">{{ trans('patient::patient.visited_clinics') }}</h5>
                            <p class="text-heading mb-3 mt-1 fs-4 fw-bold">{{ $data['totalClinics'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Examinations --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ trans('patient::patient.recent_examinations') }}</h5>
                            <a href="{{ route('patient.medical-history.index') }}" class="btn btn-sm btn-primary">
                                {{ trans('patient::patient.view_all') }}
                            </a>
                        </div>
                        <div class="card-body">
                            @if($data['recentExaminations']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('patient::patient.date') }}</th>
                                                <th>{{ trans('patient::patient.clinic') }}</th>
                                                <th>{{ trans('patient::patient.diagnosis') }}</th>
                                                <th>{{ trans('patient::patient.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['recentExaminations'] as $examination)
                                                <tr>
                                                    <td>{{ $examination->created_at->format('Y-m-d') }}</td>
                                                    <td>{{ $examination->clinic?->name ?? '-' }}</td>
                                                    <td>{{ $examination->reason_of_visiting ?? '-' }}</td>
                                                    <td>
                                                        <a href="{{ route('patient.appointments.show', $examination->id) }}"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="ti tabler-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="ti tabler-clipboard-off icon-48px text-muted mb-3"></i>
                                    <p class="text-muted">{{ trans('patient::patient.no_examinations') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
