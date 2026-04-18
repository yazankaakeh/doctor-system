@php
    $examStatusClass = $medicalExam->status?->class() ?? 'secondary';
    $examStatusLabel = $medicalExam->status?->label() ?? '—';
    $complaintPreview = \Illuminate\Support\Str::limit($medicalExam->reason_of_visiting ?? '—', 80);
    $accordionId = 'historyAccordion-' . $medicalExam->id;
@endphp

<div class="history-exam-card">
    {{-- Summary header (always visible) --}}
    <div class="history-exam-card__header">
        <div class="history-exam-card__meta">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="history-exam-card__id">#{{ $medicalExam->id }}</span>
                <span class="badge text-bg-{{ $examStatusClass }}">{{ $examStatusLabel }}</span>
                <span class="small text-muted">
                    <i class="ti tabler-calendar me-1"></i>
                    {{ $medicalExam->created_at?->format('Y-m-d H:i') }}
                </span>
            </div>
            @if($medicalExam->reason_of_visiting)
                <div class="mt-1 small">
                    <span class="text-muted">
                        {{ trans('doctor::doctor.medicalExaminations.reasonOfVisiting') }}:
                    </span>
                    <span class="fw-medium">{{ $complaintPreview }}</span>
                </div>
            @endif
        </div>
        <a href="{{ route('doctor.medicalExamination.create', ['medicalExaminationId' => $medicalExam->id]) }}"
           class="btn btn-sm btn-outline-primary">
            <i class="ti tabler-external-link me-1"></i>
            {{ trans('doctor::doctor.show') }}
        </a>
    </div>

    {{-- Accordion body --}}
    <div class="accordion accordion-flush" id="{{ $accordionId }}">

        {{-- Preview / notes --}}
        @if($medicalExam->clinical_examination || $medicalExam->impression || $medicalExam->request_for_action || $medicalExam->note || $medicalExam->medical_story)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-preview">
                        <i class="ti tabler-stethoscope me-2 text-primary"></i>
                        {{ trans('doctor::doctor.medicalExaminations.card.medicalPreview') }}
                    </button>
                </h2>
                <div id="{{ $accordionId }}-preview" class="accordion-collapse collapse"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body">
                        @if($medicalExam->medical_story)
                            <div class="info-row">
                                <span class="label">{{ trans('doctor::doctor.medicalExaminations.medicalStory') }}</span>
                            </div>
                            <p class="small mb-2">{{ $medicalExam->medical_story }}</p>
                        @endif
                        @if($medicalExam->clinical_examination)
                            <div class="info-row"><span class="label">{{ trans('doctor::doctor.medicalExaminations.clinical_examination') }}</span></div>
                            <p class="small mb-2">{{ $medicalExam->clinical_examination }}</p>
                        @endif
                        @if($medicalExam->impression)
                            <div class="info-row">
                                <span class="label">{{ trans('doctor::doctor.medicalExaminations.impression') }}</span>
                                <span class="value">{{ $medicalExam->impression }}</span>
                            </div>
                        @endif
                        @if($medicalExam->request_for_action)
                            <div class="info-row">
                                <span class="label">{{ trans('doctor::doctor.medicalExaminations.request_for_action') }}</span>
                                <span class="value">{{ $medicalExam->request_for_action }}</span>
                            </div>
                        @endif
                        @if($medicalExam->note)
                            <div class="info-row"><span class="label">{{ trans('doctor::doctor.medicalExaminations.note') }}</span></div>
                            <p class="small text-muted mb-0">{{ $medicalExam->note }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Vital signs --}}
        @if($medicalExam->vitalSigns->isNotEmpty())
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-vitals">
                        <i class="ti tabler-heartbeat me-2 text-primary"></i>
                        {{ trans('doctor::doctor.medicalExaminations.card.vitalSigns') }}
                        <span class="badge rounded-pill bg-label-secondary ms-2">
                            {{ $medicalExam->vitalSigns->count() }}
                        </span>
                    </button>
                </h2>
                <div id="{{ $accordionId }}-vitals" class="accordion-collapse collapse"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body">
                        <div class="row g-2">
                            @foreach($medicalExam->vitalSigns as $vitalSign)
                                <div class="col-sm-6">
                                    <div class="info-row">
                                        <span class="label">
                                            {{ $vitalSign->name }}
                                            @if($vitalSign->unit)
                                                <small class="text-muted">({{ $vitalSign->unit }})</small>
                                            @endif
                                        </span>
                                        <span class="value">{{ $vitalSign->pivot->value ?? '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Medicines --}}
        @if($medicalExam->medicines->isNotEmpty())
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-medicines">
                        <i class="ti tabler-pill me-2 text-primary"></i>
                        {{ trans('doctor::doctor.medicalExaminations.card.medicines') }}
                        <span class="badge rounded-pill bg-label-secondary ms-2">
                            {{ $medicalExam->medicines->count() }}
                        </span>
                    </button>
                </h2>
                <div id="{{ $accordionId }}-medicines" class="accordion-collapse collapse"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>{{ trans('doctor::doctor.medicalExaminations.drugName') }}</th>
                                    <th>{{ trans('doctor::doctor.medicalExaminations.dose') }}</th>
                                    <th>{{ trans('doctor::doctor.medicalExaminations.dosage') }}</th>
                                    <th>{{ trans('doctor::doctor.medicalExaminations.duration') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($medicalExam->medicines as $medicine)
                                    <tr>
                                        <td class="fw-medium">{{ $medicine->name }}</td>
                                        <td>{{ $medicine->pivot->dose ?? '—' }}</td>
                                        <td>{{ $medicine->pivot->dosage ?? '—' }}</td>
                                        <td>{{ $medicine->pivot->duration ?? '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="{{ route('doctor.pdf.downloadMedicines', ['id' => $medicalExam->id]) }}"
                               target="_blank">
                                <i class="ti tabler-file-type-pdf me-1"></i>
                                {{ trans('doctor::doctor.medicalExaminations.printMedicines') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tests --}}
        @if($medicalExam->medicalTests->isNotEmpty())
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-tests">
                        <i class="ti tabler-test-pipe me-2 text-primary"></i>
                        {{ trans('doctor::doctor.medicalExaminations.card.medicalTest') }}
                        <span class="badge rounded-pill bg-label-secondary ms-2">
                            {{ $medicalExam->medicalTests->count() }}
                        </span>
                    </button>
                </h2>
                <div id="{{ $accordionId }}-tests" class="accordion-collapse collapse"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>{{ trans('doctor::doctor.patients.name') }}</th>
                                    <th>{{ trans('doctor::doctor.medicalExaminations.card.medicalTestResult') }}</th>
                                    <th>{{ trans('doctor::doctor.medicalExaminations.card.medicalTestType') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($medicalExam->medicalTests as $medicalTest)
                                    <tr>
                                        <td class="fw-medium">{{ $medicalTest->name }}</td>
                                        <td>{{ $medicalTest->pivot->value ?? '—' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $medicalTest->type?->class() }}">
                                                {{ $medicalTest->type?->label() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($medicalTest->pivot?->getFirstMediaUrl('attachment'))
                                                <a target="_blank"
                                                   href="{{ $medicalTest->pivot->getFirstMediaUrl('attachment') }}"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="{{ trans('doctor::doctor.viewFile') }}">
                                                    <i class="ti tabler-file-text"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 border-top">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="{{ route('doctor.pdf.downloadMedicalTest', ['id' => $medicalExam->id]) }}"
                               target="_blank">
                                <i class="ti tabler-file-type-pdf me-1"></i>
                                {{ trans('doctor::doctor.medicalExaminations.printMedicalTests') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Final diagnosis --}}
        @if($medicalExam->finalDiagnosis->isNotEmpty())
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-diagnosis">
                        <i class="ti tabler-clipboard-check me-2 text-primary"></i>
                        {{ trans('doctor::doctor.medicalExaminations.card.finalDiagnosis') }}
                        <span class="badge rounded-pill bg-label-secondary ms-2">
                            {{ $medicalExam->finalDiagnosis->count() }}
                        </span>
                    </button>
                </h2>
                <div id="{{ $accordionId }}-diagnosis" class="accordion-collapse collapse"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($medicalExam->finalDiagnosis as $finalDiagnose)
                                <span class="badge rounded-pill bg-label-primary">
                                    <i class="ti tabler-checklist me-1"></i>{{ $finalDiagnose->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Files --}}
        @if($medicalExam->getMedia('attachments')->isNotEmpty())
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-files">
                        <i class="ti tabler-files me-2 text-primary"></i>
                        {{ trans('doctor::doctor.parts.files.title') }}
                        <span class="badge rounded-pill bg-label-secondary ms-2">
                            {{ $medicalExam->getMedia('attachments')->count() }}
                        </span>
                    </button>
                </h2>
                <div id="{{ $accordionId }}-files" class="accordion-collapse collapse"
                     data-bs-parent="#{{ $accordionId }}">
                    <div class="accordion-body">
                        @foreach($medicalExam->getMedia('attachments') as $file)
                            @includeIf('doctor::doctor.medicalExamination.partials.singleFile', ['file' => $file])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <style>
        .history-exam-card {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius-lg, 0.5rem);
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .history-exam-card__header {
            padding: .85rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid var(--bs-border-color);
            background: var(--bs-tertiary-bg);
        }
        .history-exam-card__id {
            font-weight: 600;
            color: var(--bs-primary);
        }
        .history-exam-card .accordion-item {
            border: 0;
            border-bottom: 1px solid var(--bs-border-color);
            background: transparent;
        }
        .history-exam-card .accordion-item:last-child { border-bottom: 0; }
        .history-exam-card .accordion-button {
            background: transparent;
            font-weight: 500;
            font-size: .9rem;
            padding: .65rem 1rem;
            box-shadow: none !important;
        }
        .history-exam-card .accordion-button:not(.collapsed) {
            color: var(--bs-primary);
            background: var(--bs-tertiary-bg);
        }
        .history-exam-card .accordion-body { padding: 1rem; font-size: .9rem; }
        .history-exam-card .info-row {
            display: flex; justify-content: space-between; gap: 1rem;
            padding: .4rem 0;
            font-size: .85rem;
        }
        .history-exam-card .info-row .label { color: var(--bs-secondary-color); }
        .history-exam-card .info-row .value { font-weight: 500; text-align: end; }
    </style>
@endpush
