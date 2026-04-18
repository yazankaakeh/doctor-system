@php
    use Modules\Core\app\Helpers\FileUploadHelper;
    $phone  = $patient->phone ?? '';
    $digits = FileUploadHelper::digits_only($phone);
    $waUrl  = FileUploadHelper::wa_link($phone, '90');
    $tgApp  = 'tg://resolve?phone=' . $digits;
@endphp

<div class="patient-lite">
    {{-- Identity --}}
    <div class="text-center">
        <img class="rounded-circle avatar-lg mb-3"
             src="{{ $patient->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
             alt="{{ $patient->name }}">
        <h6 class="mb-1">{{ $patient->name }}</h6>
        @if($patient?->is_active)
            <span class="badge text-bg-{{ $patient->is_active->class() }}">
                {{ $patient->is_active->label() }}
            </span>
        @endif
    </div>

    {{-- Quick stats --}}
    <div class="d-flex justify-content-center gap-4 mt-3">
        <div class="text-center">
            <div class="h6 mb-0">{{ $patient->medicalExamination?->count() ?? 0 }}</div>
            <small class="text-muted">
                {{ trans('doctor::doctor.medicalExaminations.title') }}
            </small>
        </div>
        <div class="text-center">
            <a href="{{ route('doctor.patients.show', ['id' => $patient->id]) }}"
               target="_blank"
               class="btn btn-outline-secondary btn-sm">
                <i class="ti tabler-external-link me-1"></i>
                {{ trans('doctor::doctor.show') }}
            </a>
        </div>
    </div>

    {{-- Contact actions --}}
    @if($phone)
        <div class="section-title">
            {{ trans('doctor::doctor.patients.phone') }}
        </div>
        <div class="btn-group w-100" role="group">
            <a href="tel:{{ $phone }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti tabler-phone"></i>
            </a>
            <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="ti tabler-brand-whatsapp"></i>
            </a>
            <a href="{{ $tgApp }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="ti tabler-brand-telegram"></i>
            </a>
            <span class="btn btn-sm btn-outline-secondary disabled text-truncate" style="max-width: 120px;">
                {{ $phone }}
            </span>
        </div>
    @endif

    {{-- Basic details --}}
    <div class="section-title">
        {{ trans('doctor::doctor.medicalExaminations.patientDetails') }}
    </div>
    <div class="info-row">
        <span class="label">{{ trans('doctor::doctor.id') }}</span>
        <span class="value">#{{ $patient->id }}</span>
    </div>
    @if($patient->age)
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.age') }}</span>
            <span class="value">{{ $patient->age }}</span>
        </div>
    @endif
    @if($patient?->gender?->label())
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.gender') }}</span>
            <span class="value">{{ $patient->gender->label() }}</span>
        </div>
    @endif
    @if($patient?->blood_type?->label())
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.blood_type') }}</span>
            <span class="value">{{ $patient->blood_type->label() }}</span>
        </div>
    @endif
    @if($patient?->marital_status?->label())
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.marital_status') }}</span>
            <span class="value">{{ $patient->marital_status->label() }}</span>
        </div>
    @endif
    @if($patient->children !== null && $patient->children !== '')
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.children') }}</span>
            <span class="value">{{ $patient->children }}</span>
        </div>
    @endif
    @if($patient->nationality?->name)
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.nationality_id') }}</span>
            <span class="value">{{ $patient->nationality->name }}</span>
        </div>
    @endif
    @if($patient->work)
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.work') }}</span>
            <span class="value">{{ $patient->work }}</span>
        </div>
    @endif
    @if($patient->email)
        <div class="info-row">
            <span class="label">{{ trans('doctor::doctor.patients.email') }}</span>
            <span class="value text-truncate" style="max-width: 160px;">
                <a href="mailto:{{ $patient->email }}" class="text-body">{{ $patient->email }}</a>
            </span>
        </div>
    @endif

    {{-- Medical history (only renders if something is present) --}}
    @php
        $historyItems = array_filter([
            trans('doctor::doctor.patients.drug_allergies')  => $patient->drug_allergies,
            trans('doctor::doctor.patients.disabilities')    => $patient->disabilities,
            trans('doctor::doctor.patients.medical_history') => $patient->medical_history,
            trans('doctor::doctor.patients.surgical_history')=> $patient->surgical_history,
            trans('doctor::doctor.patients.accident_history')=> $patient->accident_history,
        ], fn ($v) => !empty($v));
    @endphp
    @if(!empty($historyItems))
        <div class="section-title">
            {{ trans('doctor::doctor.patients.medical_history') }}
        </div>
        @foreach($historyItems as $label => $value)
            <div class="info-row flex-column align-items-start">
                <span class="label small mb-1">{{ $label }}</span>
                <span class="value text-start w-100" style="white-space: normal; font-weight: 400;">
                    {{ $value }}
                </span>
            </div>
        @endforeach
    @endif
</div>
