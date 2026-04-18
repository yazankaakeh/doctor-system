@php $page = 'patient-medical-history'; @endphp
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('patient::patient.sidebar.medical_history'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            {{-- Patient Info Card --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6>{{ trans('patient::patient.blood_type') }}</h6>
                                    <p class="badge bg-danger fs-6">{{ $patient->blood_type?->label() ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6>{{ trans('patient::patient.age') }}</h6>
                                    <p>{{ $patient->age ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6>{{ trans('patient::patient.gender') }}</h6>
                                    <p>{{ $patient->gender?->label() ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6>{{ trans('patient::patient.allergies') }}</h6>
                                    <p>{{ $patient->drug_allergies ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Medical History --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ trans('patient::patient.sidebar.medical_history') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($examinations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('patient::patient.date') }}</th>
                                                <th>{{ trans('patient::patient.clinic') }}</th>
                                                <th>{{ trans('patient::patient.reason_of_visiting') }}</th>
                                                <th>{{ trans('patient::patient.diagnosis') }}</th>
                                                <th>{{ trans('patient::patient.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($examinations as $examination)
                                                <tr>
                                                    <td>{{ $examination->created_at->format('Y-m-d') }}</td>
                                                    <td>{{ $examination->clinic?->name ?? '-' }}</td>
                                                    <td>{{ Str::limit($examination->reason_of_visiting, 30) ?? '-' }}</td>
                                                    <td>
                                                        @if($examination->finalDiagnosis && $examination->finalDiagnosis->count() > 0)
                                                            {{ Str::limit($examination->finalDiagnosis->pluck('name')->join(', '), 30) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
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
                                <div class="mt-3">
                                    {{ $examinations->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="ti tabler-history-off icon-48px text-muted mb-3"></i>
                                    <p class="text-muted">{{ trans('patient::patient.no_medical_history') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
