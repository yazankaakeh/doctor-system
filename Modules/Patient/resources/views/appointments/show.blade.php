@php $page = 'patient-appointments'; @endphp
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('patient::patient.appointment_details'))

@section('vendor-style')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -21px;
            top: 8px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #696cff;
            z-index: 1;
        }
        .info-card {
            border-left: 4px solid #696cff;
        }
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-2">{{ trans('patient::patient.appointment_details') }}</h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('patient.dashboard') }}">{{ trans('patient::patient.dashboard') }}</a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('patient.appointments.index') }}">{{ trans('patient::patient.my_appointments') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active">#{{ $appointment->id }}</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="{{ route('patient.appointments.prescription.download', $appointment->id) }}"
                               class="btn btn-primary me-2">
                                <i class="ti tabler-download me-1"></i>{{ trans('patient::patient.download_prescription') }}
                            </a>
                            <a href="{{ route('patient.appointments.index') }}" class="btn btn-outline-secondary">
                                <i class="ti tabler-arrow-left me-1"></i>{{ trans('patient::patient.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Main Content -->
                <div class="col-lg-8">
                    <!-- Appointment Info Card -->
                    <div class="card mb-4 info-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="ti tabler-calendar-event me-2"></i>{{ trans('patient::patient.appointment_information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('patient::patient.appointment_id') }}</label>
                                    <div class="fw-bold fs-5">#{{ $appointment->id }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('patient::patient.date') }}</label>
                                    <div class="fw-medium">{{ $appointment->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('patient::patient.doctor_name') }}</label>
                                    <div class="fw-medium">Dr. {{ $appointment->doctor->name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">{{ trans('patient::patient.clinic') }}</label>
                                    <div class="fw-medium">{{ $appointment->clinic?->name ?? '-' }}</div>
                                </div>
                                @if($appointment->reason_of_visiting)
                                    <div class="col-12">
                                        <label class="text-muted small">{{ trans('patient::patient.reason_of_visiting') }}</label>
                                        <div class="alert alert-info mb-0">
                                            <i class="ti tabler-info-circle me-2"></i>{{ $appointment->reason_of_visiting }}
                                        </div>
                                    </div>
                                @endif
                                @if($appointment->note)
                                    <div class="col-12">
                                        <label class="text-muted small">{{ trans('patient::patient.notes') }}</label>
                                        <div class="alert alert-warning mb-0">
                                            <i class="ti tabler-note me-2"></i>{{ $appointment->note }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    @if($appointment->vitalSigns && $appointment->vitalSigns->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="ti tabler-heartbeat me-2"></i>{{ trans('patient::patient.vital_signs') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($appointment->vitalSigns as $vitalSign)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="stat-card p-3 border rounded">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar flex-shrink-0 me-3">
                                                        <div class="avatar-initial bg-label-primary rounded">
                                                            <i class="ti tabler-activity"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">{{ $vitalSign->name ?? '-' }}</small>
                                                        <h5 class="mb-0">{{ $vitalSign->pivot?->value ?? '-' }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Diagnosis -->
                    @if($appointment->finalDiagnosis && $appointment->finalDiagnosis->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="ti tabler-stethoscope me-2"></i>{{ trans('patient::patient.diagnosis') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @foreach($appointment->finalDiagnosis as $diagnosis)
                                        <div class="timeline-item">
                                            <h6 class="mb-1">{{ $diagnosis->name }}</h6>
                                            @if($diagnosis->description)
                                                <p class="text-muted mb-0">{{ $diagnosis->description }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Prescribed Medicines -->
                    @if($appointment->medicines && $appointment->medicines->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="ti tabler-pill me-2"></i>{{ trans('patient::patient.prescribed_medicines') }}
                                </h5>
                                <a href="{{ route('patient.appointments.prescription.download', $appointment->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti tabler-download me-1"></i>{{ trans('patient::patient.download_pdf') }}
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('patient::patient.medicine') }}</th>
                                                <th>{{ trans('patient::patient.dosage_form') }}</th>
                                                <th>{{ trans('patient::patient.dose') }}</th>
                                                <th>{{ trans('patient::patient.dosage') }}</th>
                                                <th>{{ trans('patient::patient.duration') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($appointment->medicines as $medicine)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ti tabler-pill text-primary me-2"></i>
                                                            <strong>{{ $medicine->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>{{ $medicine->pivot->dosageForm?->name ?? '-' }}</td>
                                                    <td>{{ $medicine->pivot?->dose ?? '-' }}</td>
                                                    <td>{{ $medicine->pivot?->dosage ?? '-' }}</td>
                                                    <td>{{ $medicine->pivot?->duration ?? '-' }}</td>
                                                </tr>
                                                @if($medicine->pivot?->note)
                                                    <tr>
                                                        <td colspan="5" class="bg-light">
                                                            <small class="text-muted">
                                                                <i class="ti tabler-note me-1"></i>
                                                                {{ $medicine->pivot->note }}
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Medical Tests -->
                    @if($appointment->medicalTests && $appointment->medicalTests->count() > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="ti tabler-test-pipe me-2"></i>{{ trans('patient::patient.medical_tests') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($appointment->medicalTests as $test)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-1">{{ $test->name }}</h6>
                                                <p class="text-muted mb-0 small">
                                                    {{ trans('patient::patient.result') }}: {{ $test->pivot?->value ?? trans('patient::patient.pending') }}
                                                </p>
                                            </div>
                                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                                @php
                                                    $media = $test->pivot->getFirstMedia('attachment');
                                                @endphp

                                                @if($media)
                                                    <a href="{{ $test->pivot->getSecureMediaUrl($media) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="ti tabler-eye me-1"></i>{{ trans('patient::patient.view_result') }}
                                                    </a>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#uploadModal{{ $test->pivot->id }}">
                                                        <i class="ti tabler-upload me-1"></i>{{ trans('patient::patient.upload_result') }}
                                                    </button>

                                                    <!-- Upload Modal -->
                                                    <div class="modal fade" id="uploadModal{{ $test->pivot->id }}" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ trans('patient::patient.upload_test_result') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form action="{{ route('patient.appointments.tests.upload', ['appointmentId' => $appointment->id, 'testPivotId' => $test->pivot->id]) }}"
                                                                      method="POST"
                                                                      enctype="multipart/form-data">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">{{ trans('patient::patient.test_name') }}</label>
                                                                            <input type="text" class="form-control" value="{{ $test->name }}" disabled>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">{{ trans('patient::patient.test_result_file') }}</label>
                                                                            <input type="file"
                                                                                   name="test_result"
                                                                                   class="form-control"
                                                                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                                                   required>
                                                                            <div class="form-text">
                                                                                {{ trans('patient::patient.accepted_formats') }}: PDF, JPG, PNG, WEBP ({{ trans('patient::patient.max_size') }}: 10MB)
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                                            {{ trans('patient::patient.cancel') }}
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="ti tabler-upload me-1"></i>{{ trans('patient::patient.upload') }}
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column - Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Quick Actions Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ trans('patient::patient.quick_actions') }}</h5>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('patient.appointments.prescription.download', $appointment->id) }}"
                               class="btn btn-primary w-100 mb-2">
                                <i class="ti tabler-download me-1"></i>{{ trans('patient::patient.download_prescription') }}
                            </a>
                            <a href="{{ route('patient.appointments.index') }}"
                               class="btn btn-outline-secondary w-100">
                                <i class="ti tabler-list me-1"></i>{{ trans('patient::patient.all_appointments') }}
                            </a>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ trans('patient::patient.summary') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <div>
                                    <i class="ti tabler-heartbeat text-danger me-2"></i>
                                    <span>{{ trans('patient::patient.vital_signs') }}</span>
                                </div>
                                <span class="badge bg-label-primary">{{ $appointment->vitalSigns->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <div>
                                    <i class="ti tabler-pill text-success me-2"></i>
                                    <span>{{ trans('patient::patient.medicines') }}</span>
                                </div>
                                <span class="badge bg-label-success">{{ $appointment->medicines->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <div>
                                    <i class="ti tabler-test-pipe text-info me-2"></i>
                                    <span>{{ trans('patient::patient.medical_tests') }}</span>
                                </div>
                                <span class="badge bg-label-info">{{ $appointment->medicalTests->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="ti tabler-stethoscope text-warning me-2"></i>
                                    <span>{{ trans('patient::patient.diagnoses') }}</span>
                                </div>
                                <span class="badge bg-label-warning">{{ $appointment->finalDiagnosis->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
