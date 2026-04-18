@php
    use Modules\Core\app\Helpers\FileUploadHelper;

    $page = 'sales-dashboard';

    $phone  = $patient->phone ?? '';
    $digits = FileUploadHelper::digits_only($phone);
    $waUrl  = FileUploadHelper::wa_link($phone, '90');
    $tgApp  = 'tg://resolve?phone=' . $digits;

    // Safety-critical history: only shown when something exists
    $alerts = array_filter([
        trans('doctor::doctor.patients.drug_allergies')  => $patient->drug_allergies,
        trans('doctor::doctor.patients.disabilities')    => $patient->disabilities,
    ], fn ($v) => !empty($v));

    // "About" detail rows: auto-hide empty fields
    $detailRows = array_filter([
        trans('doctor::doctor.patients.age')             => $patient->age,
        trans('doctor::doctor.patients.gender')          => $patient?->gender?->label(),
        trans('doctor::doctor.patients.blood_type')      => $patient?->blood_type?->label(),
        trans('doctor::doctor.patients.marital_status')  => $patient?->marital_status?->label(),
        trans('doctor::doctor.patients.children')        => $patient->children !== null && $patient->children !== '' ? $patient->children : null,
        trans('doctor::doctor.patients.nationality_id')  => $patient->nationality?->name,
        trans('doctor::doctor.patients.work')            => $patient->work,
    ], fn ($v) => $v !== null && $v !== '');

    // Full medical history - shown in collapsible panel
    $historyFields = array_filter([
        trans('doctor::doctor.patients.medical_history') => $patient->medical_history,
        trans('doctor::doctor.patients.surgical_history')=> $patient->surgical_history,
        trans('doctor::doctor.patients.accident_history')=> $patient->accident_history,
    ], fn ($v) => !empty($v));

    $recentExams = $medicalExaminations->take(5);
    $attachmentsCount = $patient->getMedia('attachments')?->count() ?? 0;
@endphp

@extends('theme::user.layouts.horizontalLayout')

{{-- Vendor Styles --}}
@section('vendor-style')
    @livewireStyles
    @livewireScripts
    @vite([
        'resources/assets/vendor/libs/dropzone/dropzone.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss'
    ], 'build/modules/theme')
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/dropzone/dropzone.js'], 'build/modules/theme')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    ], 'build/modules/theme')
@endsection

{{-- Page Scripts --}}
@section('page-script')
    @includeIf('doctor::doctor.medicalExamination.modals.uploadFileModal', ['model' => $patient])
    @vite(['resources/assets/js/forms-file-upload.js'], 'build/modules/theme')

    <style>
        /* ── Design tokens shared with exam-page ── */
        .patient-show .hero {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            padding: 1.5rem;
        }
        .patient-show .hero__avatar {
            width: 88px; height: 88px; object-fit: cover;
            border: 2px solid var(--bs-border-color);
        }
        .patient-show .hero__meta {
            color: var(--bs-secondary-color); font-size: .9rem;
        }
        .patient-show .hero__meta .dot { margin: 0 .4rem; opacity: .4; }

        .patient-show .contact-row {
            display: flex; flex-wrap: wrap; gap: .5rem;
        }
        .patient-show .contact-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .35rem .7rem;
            border: 1px solid var(--bs-border-color);
            border-radius: 999px;
            color: var(--bs-body-color);
            font-size: .85rem;
            background: var(--bs-card-bg);
            text-decoration: none;
            transition: border-color .15s ease, color .15s ease;
        }
        .patient-show .contact-btn:hover {
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }
        .patient-show .contact-btn .ti { font-size: 1rem; }

        .patient-show .section {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            padding: 1.25rem;
        }
        .patient-show .section-heading {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1rem;
        }
        .patient-show .section-heading h6 { margin: 0; font-size: .95rem; }
        .patient-show .section-heading h6 .ti { color: var(--bs-primary); margin-right: .35rem; }

        /* Stat chip row */
        .patient-show .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        .patient-show .stat-chip {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            padding: 1rem;
            display: flex; align-items: center; gap: .75rem;
        }
        .patient-show .stat-chip__icon {
            width: 38px; height: 38px; border-radius: .5rem;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(var(--bs-primary-rgb), .1);
            color: var(--bs-primary);
            font-size: 1.15rem;
        }
        .patient-show .stat-chip__num  { font-size: 1.15rem; font-weight: 600; line-height: 1; }
        .patient-show .stat-chip__lbl  { font-size: .78rem; color: var(--bs-secondary-color); }

        /* Alerts banner */
        .patient-show .alert-banner {
            background: rgba(var(--bs-warning-rgb), .08);
            border: 1px solid rgba(var(--bs-warning-rgb), .35);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            padding: 1rem 1.25rem;
            display: flex; gap: .85rem;
            align-items: flex-start;
        }
        .patient-show .alert-banner .ti-alert {
            color: var(--bs-warning); font-size: 1.35rem; margin-top: 2px;
        }

        /* Exam list item (no nested accordions — just summary + action) */
        .patient-show .exam-item {
            display: flex; align-items: center; gap: 1rem;
            padding: .85rem 1rem;
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius, 0.375rem);
            margin-bottom: .6rem;
            transition: border-color .15s ease, transform .1s ease;
        }
        .patient-show .exam-item:hover {
            border-color: var(--bs-primary);
        }
        .patient-show .exam-item__date {
            font-size: .78rem; color: var(--bs-secondary-color);
            min-width: 96px;
        }
        .patient-show .exam-item__body { flex: 1; min-width: 0; }
        .patient-show .exam-item__title {
            font-size: .9rem; font-weight: 500;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .patient-show .exam-item__sub { font-size: .8rem; color: var(--bs-secondary-color); }

        /* Detail row (label/value) — reused for About section */
        .patient-show .info-row {
            display: flex; justify-content: space-between; gap: 1rem;
            padding: .55rem 0;
            border-bottom: 1px dashed var(--bs-border-color);
            font-size: .875rem;
        }
        .patient-show .info-row:last-child { border-bottom: 0; }
        .patient-show .info-row .label { color: var(--bs-secondary-color); }
        .patient-show .info-row .value { font-weight: 500; text-align: end; }

        .patient-show .empty-state {
            padding: 2rem 1rem; text-align: center; color: var(--bs-secondary-color);
        }
        .patient-show .empty-state .ti { font-size: 2rem; opacity: .45; }

        @media (max-width: 767.98px) {
            .patient-show .hero { flex-direction: column; text-align: center; }
            .patient-show .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
@endsection

@section('title', $patient->name)

@section('content')
    <div class="page-wrapper patient-show">
        <div class="content">

            {{-- Back link instead of full breadcrumb — simpler --}}
            <div class="mb-3">
                <a href="{{ route('doctor.patients.index') }}" class="small text-muted text-decoration-none">
                    <i class="ti tabler-arrow-left me-1"></i>
                    {{ trans('customer.sidebar.patients') }}
                </a>
            </div>

            {{-- ── Hero card ───────────────────────────────────────────── --}}
            <div class="hero d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <img src="{{ $patient->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                         alt="{{ $patient->name }}"
                         class="rounded-circle hero__avatar">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <h4 class="mb-0">{{ $patient->name }}</h4>
                            @if($patient?->is_active)
                                <span class="badge text-bg-{{ $patient->is_active->class() }}">
                                    {{ $patient->is_active->label() }}
                                </span>
                            @endif
                        </div>

                        <div class="hero__meta mt-1">
                            <span>#{{ $patient->id }}</span>
                            @if($patient->age)
                                <span class="dot">·</span>{{ $patient->age }} {{ trans('doctor::doctor.patients.age') }}
                            @endif
                            @if($patient?->gender?->label())
                                <span class="dot">·</span>{{ $patient->gender->label() }}
                            @endif
                            @if($patient?->blood_type?->label())
                                <span class="dot">·</span>{{ $patient->blood_type->label() }}
                            @endif
                        </div>

                        {{-- Contact chip row --}}
                        <div class="contact-row mt-3">
                            @if($phone)
                                <a class="contact-btn" href="tel:{{ $phone }}">
                                    <i class="ti tabler-phone"></i>{{ $phone }}
                                </a>
                                <a class="contact-btn" href="{{ $waUrl }}" target="_blank">
                                    <i class="ti tabler-brand-whatsapp"></i>WhatsApp
                                </a>
                                <a class="contact-btn" href="{{ $tgApp }}" target="_blank">
                                    <i class="ti tabler-brand-telegram"></i>Telegram
                                </a>
                            @endif
                            @if($patient->email)
                                <a class="contact-btn" href="mailto:{{ $patient->email }}">
                                    <i class="ti tabler-mail"></i>{{ $patient->email }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action cluster --}}
                <div class="d-flex align-items-center gap-2">
                    @can('doctor.medicalExamination.store')
                        <form method="POST"
                              action="{{ route('doctor.medicalExamination.store', ['patientId' => $patient->id]) }}"
                              class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-clipboard-heart me-1"></i>
                                {{ trans('doctor::doctor.medicalExaminations.card.createMedicalPreview') }}
                            </button>
                        </form>
                    @endcan

                    <div class="dropdown">
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="ti tabler-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button type="button" class="dropdown-item"
                                        data-bs-toggle="modal" data-bs-target="#uploadFile">
                                    <i class="ti tabler-upload me-2"></i>
                                    {{ trans('doctor::doctor.medicalExaminations.uploadFile') }}
                                </button>
                            </li>
                            <li>
                                <a target="_blank" class="dropdown-item"
                                   href="{{ route('doctor.patients.downloadVCard', ['id' => $patient->id]) }}">
                                    <i class="ti tabler-address-book me-2"></i>
                                    {{ trans('doctor::doctor.patients.downloadVCard') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ── Safety alerts (only if allergies / disabilities exist) ─────── --}}
            @if(!empty($alerts))
                <div class="alert-banner mb-4">
                    <i class="ti tabler-alert-triangle ti-alert"></i>
                    <div>
                        <div class="fw-medium mb-1">
                            {{ trans('doctor::doctor.patients.medical_history') }}
                        </div>
                        @foreach($alerts as $label => $value)
                            <div class="small">
                                <span class="text-muted">{{ $label }}:</span>
                                <span class="fw-medium">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ── Stats row ──────────────────────────────────────────── --}}
            <div class="stats-row mb-4">
                <div class="stat-chip">
                    <span class="stat-chip__icon"><i class="ti tabler-clipboard-heart"></i></span>
                    <div>
                        <div class="stat-chip__num">{{ $medicalExaminations->count() }}</div>
                        <div class="stat-chip__lbl">{{ trans('doctor::doctor.medicalExaminations.title') }}</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <span class="stat-chip__icon"><i class="ti tabler-clipboard-check"></i></span>
                    <div>
                        <div class="stat-chip__num">{{ count($patient->final_diagnosis_names ?? []) }}</div>
                        <div class="stat-chip__lbl">{{ trans('doctor::doctor.medicalExaminations.card.finalDiagnosis') }}</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <span class="stat-chip__icon"><i class="ti tabler-building-hospital"></i></span>
                    <div>
                        <div class="stat-chip__num">{{ $patient->clinics?->count() ?? 0 }}</div>
                        <div class="stat-chip__lbl">{{ trans('doctor::doctor.patients.clinics') }}</div>
                    </div>
                </div>
                <div class="stat-chip">
                    <span class="stat-chip__icon"><i class="ti tabler-files"></i></span>
                    <div>
                        <div class="stat-chip__num">{{ $attachmentsCount }}</div>
                        <div class="stat-chip__lbl">{{ trans('doctor::doctor.parts.files.title') }}</div>
                    </div>
                </div>
            </div>

            {{-- ── Main two-column content ─────────────────────────────── --}}
            <div class="row g-4">

                {{-- LEFT: About patient + medical history --}}
                <div class="col-lg-4">
                    <div class="section">
                        <div class="section-heading">
                            <h6>
                                <i class="ti tabler-user"></i>
                                {{ trans('doctor::doctor.medicalExaminations.patientDetails') }}
                            </h6>
                        </div>
                        @if(empty($detailRows))
                            <div class="empty-state">
                                <i class="ti tabler-user-off"></i>
                                <div class="small mt-2">—</div>
                            </div>
                        @else
                            @foreach($detailRows as $label => $value)
                                <div class="info-row">
                                    <span class="label">{{ $label }}</span>
                                    <span class="value">{{ $value }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- Medical history (collapsible if bulky) --}}
                    @if(!empty($historyFields))
                        <div class="section mt-4">
                            <div class="section-heading">
                                <h6>
                                    <i class="ti tabler-notes-medical"></i>
                                    {{ trans('doctor::doctor.patients.medical_history') }}
                                </h6>
                            </div>
                            @foreach($historyFields as $label => $value)
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">{{ $label }}</div>
                                    <div class="small" style="white-space: pre-wrap;">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Clinics --}}
                    @if($patient->clinics?->isNotEmpty())
                        <div class="section mt-4">
                            <div class="section-heading">
                                <h6>
                                    <i class="ti tabler-building-hospital"></i>
                                    {{ trans('doctor::doctor.patients.clinics') }}
                                </h6>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($patient->clinics as $clinic)
                                    <span class="badge rounded-pill bg-label-primary">
                                        <i class="ti tabler-building-hospital me-1"></i>{{ $clinic->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- RIGHT: Recent examinations + diagnoses --}}
                <div class="col-lg-8">
                    <div class="section">
                        <div class="section-heading">
                            <h6>
                                <i class="ti tabler-clipboard-heart"></i>
                                {{ trans('doctor::doctor.medicalExaminations.history') }}
                            </h6>
                            @if($medicalExaminations->count() > 5)
                                <a href="{{ route('doctor.medicalExamination.index', ['patient_id' => $patient->id]) }}"
                                   class="small text-decoration-none">
                                    {{ trans('doctor::doctor.show') }}
                                    <i class="ti tabler-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>

                        @if($recentExams->isEmpty())
                            <div class="empty-state">
                                <i class="ti tabler-clipboard-off"></i>
                                <div class="mt-2">{{ trans('doctor::doctor.medicalExaminations.empty') }}</div>
                                @can('doctor.medicalExamination.store')
                                    <form method="POST"
                                          action="{{ route('doctor.medicalExamination.store', ['patientId' => $patient->id]) }}"
                                          class="m-0 mt-3">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="ti tabler-plus me-1"></i>
                                            {{ trans('doctor::doctor.medicalExaminations.card.createMedicalPreview') }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @else
                            @foreach($recentExams as $exam)
                                <a href="{{ route('doctor.medicalExamination.create', $exam->id) }}"
                                   class="exam-item text-decoration-none text-body">
                                    <div class="exam-item__date">
                                        <i class="ti tabler-calendar me-1"></i>
                                        {{ $exam->created_at?->format('Y-m-d') }}
                                    </div>
                                    <div class="exam-item__body">
                                        <div class="exam-item__title">
                                            {{ $exam->reason_of_visiting ?: trans('doctor::doctor.medicalExaminations.medicalPreviewInfo') }}
                                        </div>
                                        <div class="exam-item__sub d-flex align-items-center flex-wrap gap-2 mt-1">
                                            @if($exam->status)
                                                <span class="badge text-bg-{{ $exam->status->class() }}">
                                                    {{ $exam->status->label() }}
                                                </span>
                                            @endif
                                            @if($exam->vitalSigns->isNotEmpty())
                                                <span class="text-muted">
                                                    <i class="ti tabler-heartbeat me-1"></i>{{ $exam->vitalSigns->count() }}
                                                </span>
                                            @endif
                                            @if($exam->medicines->isNotEmpty())
                                                <span class="text-muted">
                                                    <i class="ti tabler-pill me-1"></i>{{ $exam->medicines->count() }}
                                                </span>
                                            @endif
                                            @if($exam->medicalTests->isNotEmpty())
                                                <span class="text-muted">
                                                    <i class="ti tabler-test-pipe me-1"></i>{{ $exam->medicalTests->count() }}
                                                </span>
                                            @endif
                                            @if($exam->finalDiagnosis->isNotEmpty())
                                                <span class="text-muted">
                                                    <i class="ti tabler-clipboard-check me-1"></i>{{ $exam->finalDiagnosis->count() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <i class="ti tabler-chevron-right text-muted"></i>
                                </a>
                            @endforeach
                        @endif
                    </div>

                    {{-- Final diagnoses --}}
                    @if(!empty($patient->final_diagnosis_names))
                        <div class="section mt-4">
                            <div class="section-heading">
                                <h6>
                                    <i class="ti tabler-clipboard-check"></i>
                                    {{ trans('doctor::doctor.medicalExaminations.card.finalDiagnosis') }}
                                </h6>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($patient->final_diagnosis_names as $finalDiagnose)
                                    <span class="badge rounded-pill bg-label-primary px-3 py-2">
                                        <i class="ti tabler-checklist me-1"></i>{{ $finalDiagnose }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Files --}}
                    @if($attachmentsCount > 0)
                        <div class="section mt-4">
                            <div class="section-heading">
                                <h6>
                                    <i class="ti tabler-files"></i>
                                    {{ trans('doctor::doctor.parts.files.title') }}
                                    <span class="badge rounded-pill bg-label-secondary ms-2">
                                        {{ $attachmentsCount }}
                                    </span>
                                </h6>
                            </div>
                            @foreach($patient->getMedia('attachments') as $file)
                                @includeIf('doctor::doctor.medicalExamination.partials.singleFile', ['file' => $file])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
