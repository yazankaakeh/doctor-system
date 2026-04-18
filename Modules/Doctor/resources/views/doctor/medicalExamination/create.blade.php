@php
    $page = 'sales-dashboard';
    $statusClass = $medicalExamination->status?->class() ?? 'secondary';
    $statusLabel = $medicalExamination->status?->label() ?? '—';
@endphp
@extends('theme::user.layouts.horizontalLayout')

{{-- Vendor Styles --}}
@section('vendor-style')
    @livewireStyles
    @livewireScripts
    @includeIf('doctor::doctor.finalDiagnosis.modals.createModal')
    @includeIf('doctor::doctor.medicalTest.modals.createModal')
    @includeIf('doctor::doctor.medicine.modals.createModal')
    @includeIf('doctor::doctor.medicalExamination.modals.uploadFileModal',['model' => $medicalExamination])

    @vite([
        'resources/assets/vendor/libs/dropzone/dropzone.scss',
        'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss'
    ], 'build/modules/theme')
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('.select2').each(function () {
                $(this).select2({ allowClear: true, tags: false });
            });
        });
    </script>
    @vite(['resources/assets/vendor/libs/dropzone/dropzone.js'], 'build/modules/theme')
    @vite([
        'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js'
    ], 'build/modules/theme')
@endsection

{{-- Page Scripts --}}
@section('page-script')
    <script src="{{ asset('livewire-select2/livewire-select2.js') }}"></script>
    @vite(['resources/assets/js/forms-file-upload.js'], 'build/modules/theme')

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                if (window.Swal) {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: @json(trans('core::video.link_copied')),
                        showConfirmButton: false, timer: 2000, timerProgressBar: true,
                    });
                }
            }).catch(err => console.error('Failed to copy:', err));
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Restore the active tab from the URL hash so refreshes / livewire updates don't kick the doctor back to tab 1
            const hash = window.location.hash;
            if (hash) {
                const trigger = document.querySelector('button[data-bs-target="' + hash + '"]');
                if (trigger) new bootstrap.Tab(trigger).show();
            }
            document.querySelectorAll('#examTabs button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', e => {
                    const target = e.target.getAttribute('data-bs-target');
                    if (target) history.replaceState(null, '', target);
                });
            });
        });
    </script>

    <style>
        /* ---------- Clean / neutral polish for the examination editor ---------- */
        .exam-page .page-header {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            padding: 1rem 1.25rem;
        }
        .exam-page .page-header .exam-id {
            color: var(--bs-primary);
            font-weight: 600;
        }
        .exam-page .patient-lite {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            padding: 1.25rem;
        }
        .exam-page .patient-lite .avatar-lg {
            width: 84px; height: 84px; object-fit: cover;
            border: 2px solid var(--bs-border-color);
        }
        .exam-page .info-row {
            display: flex; justify-content: space-between; gap: 1rem;
            padding: .55rem 0;
            border-bottom: 1px dashed var(--bs-border-color);
            font-size: .875rem;
        }
        .exam-page .info-row:last-child { border-bottom: 0; }
        .exam-page .info-row .label { color: var(--bs-secondary-color); }
        .exam-page .info-row .value { color: var(--bs-body-color); font-weight: 500; text-align: end; }

        .exam-page .section-title {
            font-size: .75rem; text-transform: uppercase; letter-spacing: .06em;
            color: var(--bs-secondary-color); font-weight: 600;
            margin: 1.5rem 0 .5rem;
        }

        /* Pill-style tab nav that uses the theme primary color */
        .exam-page .nav-exam {
            gap: .25rem; padding: .25rem;
            background: var(--bs-tertiary-bg);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
        }
        .exam-page .nav-exam .nav-link {
            border: 0 !important; background: transparent;
            color: var(--bs-body-color); font-weight: 500;
            border-radius: var(--bs-border-radius, 0.375rem);
            padding: .5rem .9rem;
        }
        .exam-page .nav-exam .nav-link:hover { background: var(--bs-card-bg); }
        .exam-page .nav-exam .nav-link.active {
            background: var(--bs-primary);
            color: #fff !important;
            box-shadow: 0 1px 2px rgba(0,0,0,.08);
        }
        .exam-page .nav-exam .nav-link .ti { margin-right: .35rem; }

        .exam-page .history-card { border: 1px solid var(--bs-border-color); }
        .exam-page .history-card .nav-tabs { background: var(--bs-tertiary-bg); }

        /* Tidy up the sticky action bar */
        .exam-page .action-bar .btn { white-space: nowrap; }

        @media (max-width: 991.98px) {
            .exam-page .page-header { flex-direction: column; gap: .75rem; align-items: flex-start !important; }
        }

        /* ---------- Top live-consultation panel (video + chat) ---------- */
        .exam-page .consultation-panel {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            overflow: hidden;
        }
        .exam-page .consultation-video {
            padding: 1.25rem;
            border-right: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
        }
        .exam-page .consultation-chat {
            padding: 0;
            max-height: 340px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .exam-page .consultation-chat > * { flex: 1; min-height: 0; }
        /* Tame the chat card so it fits the compact panel height */
        .exam-page .consultation-chat .card {
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            margin: 0 !important;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .exam-page .consultation-chat .card-body {
            flex: 1;
            min-height: 0;
            overflow: auto;
        }

        @media (max-width: 991.98px) {
            .exam-page .consultation-video { border-right: 0; border-bottom: 1px solid var(--bs-border-color); }
            .exam-page .consultation-chat  { max-height: 420px; }
        }
    </style>
@endsection

@section('title', trans('doctor::doctor.medicalExaminations.title'))

@section('content')
    <div class="page-wrapper exam-page">
        <div class="content">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('doctor.dashboard') }}">{{ trans('customer.sidebar.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('doctor.medicalExamination.index') }}">
                            {{ trans('doctor::doctor.medicalExaminations.title') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        #{{ $medicalExamination->id }}
                    </li>
                </ol>
            </nav>

            {{-- Page Header --}}
            <div class="page-header d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <img
                        src="{{ $patient->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                        alt="{{ $patient->name }}"
                        class="rounded-circle"
                        style="width: 52px; height: 52px; object-fit: cover; border: 1px solid var(--bs-border-color);">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="mb-0">{{ $patient->name }}</h5>
                            <span class="badge text-bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            <span class="exam-id">#{{ $medicalExamination->id }}</span>
                            @if($patient->age)
                                <span class="mx-1">·</span>{{ $patient->age }} {{ trans('doctor::doctor.patients.age') }}
                            @endif
                            @if($patient?->gender?->label())
                                <span class="mx-1">·</span>{{ $patient->gender->label() }}
                            @endif
                            <span class="mx-1">·</span>
                            <i class="ti tabler-calendar me-1"></i>{{ $medicalExamination->created_at?->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>

                <div class="action-bar d-flex align-items-center gap-2 flex-wrap">
                    @if(isset($booking) && $booking && $booking->isConfirmed() && $booking->hasMeetingRoom())
                        <a href="{{ route('video.join', ['roomName' => $booking->getMeetingRoomName(), 'booking' => $booking->id]) }}"
                           target="_blank"
                           class="btn btn-outline-primary btn-sm">
                            <i class="ti tabler-video me-1"></i>
                            {{ trans('booking::booking.join_consultation') }}
                        </a>
                    @endif

                    <div class="dropdown">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti tabler-printer me-1"></i>
                            {{ trans('doctor::doctor.medicalExaminations.print') ?? 'Print' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header small">{{ trans('doctor::doctor.medicalExaminations.printMedicalTests') }}</li>
                            @foreach(['A5','A4','A3'] as $size)
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="{{ route('doctor.pdf.downloadMedicalTest', ['id' => $medicalExamination->id, 'pageSize' => $size]) }}">
                                        <i class="ti tabler-file-type-pdf me-2"></i>{{ $size }}
                                    </a>
                                </li>
                            @endforeach
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-header small">{{ trans('doctor::doctor.medicalExaminations.printMedicines') }}</li>
                            @foreach(['A5','A4','A3'] as $size)
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="{{ route('doctor.pdf.downloadMedicines', ['id' => $medicalExamination->id, 'pageSize' => $size]) }}">
                                        <i class="ti tabler-file-type-pdf me-2"></i>{{ $size }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <button type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#uploadFile">
                        <i class="ti tabler-upload me-1"></i>
                        {{ trans('doctor::doctor.medicalExaminations.uploadFile') }}
                    </button>
                </div>
            </div>

            {{-- ─── Top live-consultation panel (only when booking is active) ─── --}}
            @if(isset($booking) && $booking && $booking->isConfirmed())
                <div class="consultation-panel mb-4">
                    <div class="row g-0">
                        {{-- Video quick-actions --}}
                        <div class="col-lg-4 col-xl-3 consultation-video">
                            <div class="section-title mt-0 mb-3">
                                <i class="ti tabler-video me-1"></i>
                                {{ trans('core::video.video_consultation') }}
                            </div>
                            @if($booking->hasMeetingRoom())
                                <a href="{{ route('video.join', ['roomName' => $booking->getMeetingRoomName(), 'booking' => $booking->id]) }}"
                                   target="_blank"
                                   class="btn btn-primary w-100 mb-2">
                                    <i class="ti tabler-video me-1"></i>
                                    {{ trans('booking::booking.join_consultation') }}
                                </a>
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm w-100"
                                        onclick="copyToClipboard('{{ $booking->getMeetingLink() }}')">
                                    <i class="ti tabler-copy me-1"></i>
                                    {{ trans('core::video.copy_link') }}
                                </button>
                            @else
                                <p class="small text-muted mb-0">
                                    {{ trans('booking::booking.no_meeting_scheduled') }}
                                </p>
                            @endif

                            @if($booking->start_time)
                                <div class="small text-muted mt-3 pt-3 border-top">
                                    <i class="ti tabler-calendar me-1"></i>
                                    {{ $booking->booking_date?->format('Y-m-d') }}
                                    <span class="mx-1">·</span>
                                    <i class="ti tabler-clock me-1"></i>
                                    {{ $booking->start_time?->format('H:i') }} – {{ $booking->end_time?->format('H:i') }}
                                </div>
                            @endif
                        </div>

                        {{-- Chat --}}
                        @if(class_exists('\Modules\Messaging\Services\ConversationService'))
                            <div class="col-lg-8 col-xl-9 consultation-chat">
                                @livewire('booking::booking-conversation', ['booking' => $booking, 'userType' => 'doctor'])
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Two-column layout --}}
            <div class="row g-4">

                {{-- ─── Sidebar (patient card only) ─── --}}
                <div class="col-lg-3">
                    @includeIf('doctor::doctor.medicalExamination.partials.patientCard', ['patient' => $patient])
                </div>

                {{-- ─── Main content ─── --}}
                <div class="col-lg-9">
                    <div class="card shadow-none border">
                        <div class="card-body p-3">
                            <ul class="nav nav-exam flex-wrap mb-4" id="examTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab"
                                            data-bs-target="#tab-preview" type="button" role="tab">
                                        <i class="ti tabler-stethoscope"></i>
                                        {{ trans('doctor::doctor.medicalExaminations.medicalPreviewInfo') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#tab-vitals" type="button" role="tab">
                                        <i class="ti tabler-heartbeat"></i>
                                        {{ trans('doctor::doctor.medicalExaminations.vitalSignsInfo') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#tab-tests" type="button" role="tab">
                                        <i class="ti tabler-test-pipe"></i>
                                        {{ trans('doctor::doctor.medicalExaminations.card.medicalTest') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#tab-medicines" type="button" role="tab">
                                        <i class="ti tabler-pill"></i>
                                        {{ trans('doctor::doctor.medicalExaminations.card.medicines') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#tab-diagnosis" type="button" role="tab">
                                        <i class="ti tabler-clipboard-check"></i>
                                        {{ trans('doctor::doctor.medicalExaminations.card.finalDiagnosis') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab"
                                            data-bs-target="#tab-files" type="button" role="tab">
                                        <i class="ti tabler-files"></i>
                                        {{ trans('doctor::doctor.parts.files.title') }}
                                    </button>
                                </li>
                                @if($medicalExaminations->isNotEmpty())
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#tab-history" type="button" role="tab">
                                            <i class="ti tabler-history"></i>
                                            {{ trans('doctor::doctor.medicalExaminations.history') ?? 'History' }}
                                            <span class="badge rounded-pill bg-label-secondary ms-1">
                                                {{ $medicalExaminations->count() }}
                                            </span>
                                        </button>
                                    </li>
                                @endif
                            </ul>

                            <div class="tab-content p-0">
                                {{-- Preview / examination notes --}}
                                <div class="tab-pane fade show active" id="tab-preview" role="tabpanel">
                                    @includeIf('doctor::doctor.medicalExamination.partials.medicalPreview', ['medicalExamination' => $medicalExamination])
                                </div>

                                {{-- Vital signs --}}
                                <div class="tab-pane fade" id="tab-vitals" role="tabpanel">
                                    <livewire:doctor::vital-signs-livewire :medicalExamination="$medicalExamination" />
                                </div>

                                {{-- Medical tests (lab + radiology) --}}
                                <div class="tab-pane fade" id="tab-tests" role="tabpanel">
                                    <div class="row g-4">
                                        <div class="col-xl-6">
                                            <livewire:doctor::medical-tests-livewire
                                                :medicalExamination="$medicalExamination"
                                                :title="trans('doctor::doctor.medicalExaminations.laboratoryTests')"
                                                :type="\Modules\Doctor\Enums\MedicalTestTypeEnum::LABORATORY_TESTS"
                                                name="laboratoryTests" />
                                        </div>
                                        <div class="col-xl-6">
                                            <livewire:doctor::medical-tests-livewire
                                                :medicalExamination="$medicalExamination"
                                                :title="trans('doctor::doctor.medicalExaminations.radiologyTests')"
                                                :type="\Modules\Doctor\Enums\MedicalTestTypeEnum::RADIOLOGY_TESTS"
                                                name="radiologyTests" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Medicines --}}
                                <div class="tab-pane fade" id="tab-medicines" role="tabpanel">
                                    <livewire:doctor::medicines-info-livewire :medicalExamination="$medicalExamination" />
                                </div>

                                {{-- Final diagnosis --}}
                                <div class="tab-pane fade" id="tab-diagnosis" role="tabpanel">
                                    <livewire:doctor::final-diagnosis-patient-livewire
                                        :patientId="$patient->id"
                                        name="finalDiagnose"
                                        :medicalExamination="$medicalExamination" />
                                </div>

                                {{-- Files --}}
                                <div class="tab-pane fade" id="tab-files" role="tabpanel">
                                    @includeIf('doctor::doctor.medicalExamination.partials.files', ['model' => $medicalExamination])
                                </div>

                                {{-- Previous examinations --}}
                                @if($medicalExaminations->isNotEmpty())
                                    <div class="tab-pane fade" id="tab-history" role="tabpanel">
                                        <div class="section-title mt-0">
                                            {{ trans('doctor::doctor.medicalExaminations.history') ?? 'Previous examinations' }}
                                        </div>
                                        <div class="row g-4">
                                            @foreach($medicalExaminations as $medicalExam)
                                                <div class="col-12 col-xl-6 history-card">
                                                    @includeIf('doctor::doctor.medicalExamination.partials.medicalExamination', ['medicalExam' => $medicalExam])
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
