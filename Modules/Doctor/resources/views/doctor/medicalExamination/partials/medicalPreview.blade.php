{{-- Medical preview / examination notes form --}}
<form action="{{ route('doctor.medicalExamination.submit') }}" method="POST" class="medical-preview-form" novalidate>
    @csrf
    <input type="hidden" value="{{ $medicalExamination->id }}" name="id">

    {{-- Group: complaint & story --}}
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <i class="ti tabler-message-circle-question text-primary me-2"></i>
            <h6 class="mb-0">
                {{ trans('doctor::doctor.medicalExaminations.medicalPreviewInfo') }}
            </h6>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <x-core::input
                    label="doctor::doctor.medicalExaminations.reasonOfVisiting"
                    id="reason_of_visiting"
                    name="reason_of_visiting"
                    type="text"
                    model="reason_of_visiting"
                    :value="old('reason_of_visiting', $medicalExamination->reason_of_visiting)"/>
            </div>
            <div class="col-12">
                <x-core::textarea
                    label="doctor::doctor.medicalExaminations.medicalStory"
                    id="medical_story"
                    name="medical_story"
                    type="text"
                    model="medical_Story"
                    :value="old('medical_story', $medicalExamination->medical_story)"/>
            </div>
        </div>
    </div>

    {{-- Group: clinical findings --}}
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <i class="ti tabler-stethoscope text-primary me-2"></i>
            <h6 class="mb-0">
                {{ trans('doctor::doctor.medicalExaminations.clinical_examination') }}
            </h6>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <x-core::textarea
                    label="doctor::doctor.medicalExaminations.clinical_examination"
                    id="clinical_examination"
                    name="clinical_examination"
                    type="text"
                    model="clinical_examination"
                    :value="old('clinical_examination', $medicalExamination->clinical_examination)"/>
            </div>

            <div class="col-md-6">
                <x-core::input
                    label="doctor::doctor.medicalExaminations.impression"
                    id="impression"
                    name="impression"
                    type="text"
                    model="impression"
                    :value="old('impression', $medicalExamination->impression)"/>
            </div>

            <div class="col-md-6">
                <x-core::input
                    label="doctor::doctor.medicalExaminations.request_for_action"
                    id="request_for_action"
                    name="request_for_action"
                    type="text"
                    model="request_for_action"
                    :value="old('request_for_action', $medicalExamination->request_for_action)"/>
            </div>
        </div>
    </div>

    {{-- Group: notes --}}
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <i class="ti tabler-notes text-primary me-2"></i>
            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.note') }}</h6>
        </div>

        <x-core::textarea
            label="doctor::doctor.medicalExaminations.note"
            id="note"
            name="note"
            type="text"
            model="note"
            :value="old('note', $medicalExamination->note)"/>
    </div>

    {{-- Action bar --}}
    <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top">
        <small class="text-muted me-auto">
            <i class="ti tabler-clock me-1"></i>
            {{ trans('doctor::doctor.medicalExaminations.createdAt') }}:
            {{ $medicalExamination->updated_at?->format('Y-m-d H:i') ?? '—' }}
        </small>
        <button type="submit" class="btn btn-primary">
            <i class="ti tabler-device-floppy me-1"></i>
            {{ trans('doctor::doctor.save') }}
        </button>
    </div>
</form>
