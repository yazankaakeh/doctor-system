<?php
/*
|--------------------------------------------------------------------------
| Medical Examination – Read-only detail page
|--------------------------------------------------------------------------
|
| Rendered by MedicalExaminationController@show.
|
| Context variables supplied by the controller:
|   $medicalExamination   - MedicalExamination with eager-loaded relations
|                           (patient, doctor, clinic, vitalSigns,
|                           medicines.dosageForm, medicalTests, finalDiagnosis,
|                           media).
|   $patient              - shortcut to $medicalExamination->patient.
|   $medicalExaminations  - collection of this patient's OTHER examinations
|                           (used for the history panel at the bottom).
|   $dosageForms          - Collection keyed by id for resolving a
|                           prescription's dosage_form_id (from the pivot)
|                           to a human-readable DosageForm name.
|
| Layout:
|   Left column  → patient card (partial)
|   Right column → examination summary, vital signs, diagnoses,
|                  medicines, medical tests, attachments, history
*/
$page = 'sales-dashboard';
?>
@extends('theme::user.layouts.horizontalLayout')

{{-- Page styling (scoped info-row helpers reused by patientCard partial). --}}
@section('vendor-style')
    <style>
        .patient-lite .section-title { font-size: .75rem; text-transform: uppercase; color: var(--bs-secondary-color); margin-top: 1rem; margin-bottom: .5rem; }
        .patient-lite .info-row { display: flex; justify-content: space-between; padding: .25rem 0; font-size: .875rem; }
        .patient-lite .info-row .label { color: var(--bs-secondary-color); }
        .patient-lite .info-row .value { font-weight: 500; }
        .exam-field-label { font-size: .75rem; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .03em; }
        .exam-field-value { white-space: pre-line; }
    </style>
@endsection

@section('title', trans('doctor::doctor.medicalExaminations.title'))

@section('content')
    <div class="page-wrapper">
        <div class="content">

            {{-- Flash messages. --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti tabler-check me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti tabler-alert-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Top bar: back + edit + status badge. --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('doctor.medicalExamination.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ti tabler-arrow-left me-1"></i>
                        {{ trans('booking::booking.back') }}
                    </a>
                    <h5 class="mb-0">
                        {{ trans('doctor::doctor.medicalExaminations.title') }}
                        <span class="text-muted">#{{ $medicalExamination->id }}</span>
                    </h5>
                    @if($medicalExamination->status)
                        <span class="badge text-bg-{{ $medicalExamination->status->class() }}">
                            {{ $medicalExamination->status->label() }}
                        </span>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    @can('doctor.medicalExamination.create')
                        <a href="{{ route('doctor.medicalExamination.create', $medicalExamination->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="ti tabler-edit me-1"></i>
                            {{ trans('doctor::doctor.edit') }}
                        </a>
                    @endcan
                </div>
            </div>

            <div class="row g-4">
                {{-- ====== LEFT: patient card ====== --}}
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            @if($patient)
                                {{-- Reuse the shared patient card partial (identity + contacts + medical history). --}}
                                @include('doctor::doctor.medicalExamination.partials.patientCard', ['patient' => $patient])
                            @else
                                <p class="text-muted mb-0">{{ trans('doctor::doctor.medicalExaminations.empty') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ====== RIGHT: examination content ====== --}}
                <div class="col-lg-8">

                    {{-- --- Examination meta (doctor, clinic, created at) --- --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <i class="ti tabler-stethoscope text-primary me-2"></i>
                            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.medicalPreviewInfo') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="exam-field-label">{{ trans('customer.sidebar.clinic') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->clinic?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    {{-- Examining doctor — prefixed with "Dr." like the PDF templates. --}}
                                    <div class="exam-field-label">Doctor</div>
                                    <div class="exam-field-value">
                                        @if($medicalExamination->doctor)
                                            Dr. {{ $medicalExamination->doctor->name }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.createdAt') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.reasonOfVisiting') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->reason_of_visiting ?: '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.medicalStory') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->medical_story ?: '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.clinical_examination') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->clinical_examination ?: '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.impression') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->impression ?: '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.request_for_action') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->request_for_action ?: '-' }}</div>
                                </div>

                                <div class="col-12">
                                    <div class="exam-field-label">{{ trans('doctor::doctor.medicalExaminations.note') }}</div>
                                    <div class="exam-field-value">{{ $medicalExamination->note ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- --- Vital signs --- --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <i class="ti tabler-activity-heartbeat text-primary me-2"></i>
                            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.card.vitalSigns') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($medicalExamination->vitalSigns->isEmpty())
                                <p class="text-muted mb-0">-</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('doctor::doctor.medicine.name') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.value', ['name' => '']) }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {{-- Each vital sign carries the recorded value on the pivot row. --}}
                                        @foreach($medicalExamination->vitalSigns as $vitalSign)
                                            <tr>
                                                <td>{{ $vitalSign->name }}</td>
                                                <td>{{ $vitalSign->pivot->value ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- --- Final diagnoses --- --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <i class="ti tabler-clipboard-check text-primary me-2"></i>
                            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.finalDiagnosisInfo') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($medicalExamination->finalDiagnosis->isEmpty())
                                <p class="text-muted mb-0">-</p>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($medicalExamination->finalDiagnosis as $diagnosis)
                                        <span class="badge bg-label-primary">{{ $diagnosis->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- --- Medicines (prescribed) --- --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center">
                            <i class="ti tabler-pill text-primary me-2"></i>
                            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.medicines') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($medicalExamination->medicines->isEmpty())
                                <p class="text-muted mb-0">-</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.drugName') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.dose') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.dosage') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.howToDrink') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.duration') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.note') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {{-- Pivot columns carry the per-prescription dose/dosage/duration/note. --}}
                                        @foreach($medicalExamination->medicines as $medicine)
                                            <tr>
                                                <td>{{ $medicine->name }}</td>
                                                <td>{{ $medicine->pivot->dose ?? '-' }}</td>
                                                <td>{{ $medicine->pivot->dosage ?? '-' }}</td>
                                                {{--
                                                    The prescription's dosage form lives on the pivot as
                                                    dosage_form_id. Resolve it through the pre-built map
                                                    passed from the controller so we don't query per row.
                                                --}}
                                                <td>{{ optional($dosageForms[$medicine->pivot->dosage_form_id] ?? null)->name ?? '-' }}</td>
                                                <td>{{ $medicine->pivot->duration ?? '-' }}</td>
                                                <td>{{ $medicine->pivot->note ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- --- Medical tests (laboratory + radiology buckets) --- --}}
                    @php
                        // Split medicalTests into laboratory vs radiology groups so we
                        // can render them under clearer headings. Uses the `type`
                        // column (MedicalTestTypeEnum) on the MedicalTest model.
                        $laboratoryTests = $medicalExamination->medicalTests->filter(
                            fn ($t) => (int) ($t->type?->value ?? $t->type) === \Modules\Doctor\Enums\MedicalTestTypeEnum::LABORATORY_TESTS->value
                        );
                        $radiologyTests = $medicalExamination->medicalTests->filter(
                            fn ($t) => (int) ($t->type?->value ?? $t->type) === \Modules\Doctor\Enums\MedicalTestTypeEnum::RADIOLOGY_TESTS->value
                        );
                    @endphp

                    <div class="row g-4 mb-4">
                        {{-- Laboratory tests column. --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center">
                                    <i class="ti tabler-test-pipe text-primary me-2"></i>
                                    <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.laboratoryTests') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($laboratoryTests->isEmpty())
                                        <p class="text-muted mb-0">-</p>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($laboratoryTests as $test)
                                                <li class="d-flex justify-content-between py-1 border-bottom">
                                                    <span>{{ $test->name }}</span>
                                                    <span class="text-muted">{{ $test->pivot->value ?? '-' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Radiology tests column. --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center">
                                    <i class="ti tabler-radioactive text-primary me-2"></i>
                                    <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.radiologyTests') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($radiologyTests->isEmpty())
                                        <p class="text-muted mb-0">-</p>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($radiologyTests as $test)
                                                <li class="d-flex justify-content-between py-1 border-bottom">
                                                    <span>{{ $test->name }}</span>
                                                    <span class="text-muted">{{ $test->pivot->value ?? '-' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- --- Attached files (media library, secure disk) --- --}}
                    @if($medicalExamination->getMedia('attachments')->isNotEmpty())
                        {{--
                            Reuse the shared files partial; it expects the examination
                            in a `$model` variable so we alias it here.
                        --}}
                        @include('doctor::doctor.medicalExamination.partials.files', ['model' => $medicalExamination])
                    @endif

                    {{-- --- Previous examinations for this patient --- --}}
                    @if($medicalExaminations->isNotEmpty())
                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center">
                                <i class="ti tabler-history text-primary me-2"></i>
                                <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.history') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('doctor::doctor.id') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.createdAt') }}</th>
                                                <th>{{ trans('doctor::doctor.medicalExaminations.reasonOfVisiting') }}</th>
                                                <th>{{ trans('customer.account.status') }}</th>
                                                <th class="text-end">{{ trans('admin.audits.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($medicalExaminations as $previous)
                                            <tr>
                                                <td>#{{ $previous->id }}</td>
                                                <td>{{ $previous->created_at?->format('Y-m-d H:i') }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($previous->reason_of_visiting ?? '-', 40) }}</td>
                                                <td>
                                                    @if($previous->status)
                                                        <span class="badge text-bg-{{ $previous->status->class() }}">
                                                            {{ $previous->status->label() }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @can('doctor.medicalExamination.show')
                                                        <a href="{{ route('doctor.medicalExamination.show', $previous->id) }}"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="ti tabler-eye"></i>
                                                        </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
