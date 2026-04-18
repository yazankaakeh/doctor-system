<div wire:ignore.self data-livewire="{{ $componentName }}" class="medicines-block">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center">
            <i class="ti tabler-pill text-primary me-2"></i>
            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.medicines') }}</h6>
            <small class="text-muted ms-2">
                ({{ count($medicinesData) }})
            </small>
        </div>
        <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    wire:click="increase()">
                <i class="ti tabler-plus me-1"></i>
                {{ trans('doctor::doctor.medicalExaminations.drugName') }}
            </button>
            <button type="button"
                    class="btn btn-sm btn-primary"
                    wire:click="save">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ trans('doctor::doctor.save') }}
            </button>
        </div>
    </div>

    @if(empty($medicinesData))
        <div class="text-center py-4 text-muted">
            <i class="ti tabler-pill" style="font-size: 2rem;"></i>
            <div class="mt-2">{{ trans('doctor::doctor.medicalExaminations.medicines') }}</div>
            <button type="button" class="btn btn-sm btn-primary mt-3" wire:click="increase()">
                <i class="ti tabler-plus me-1"></i>
                {{ trans('doctor::doctor.create') }}
            </button>
        </div>
    @else
        <div class="medicines-list">
            @foreach($medicinesData as $index => $medicine)
                <div class="medicine-row">
                    <div class="medicine-row__header">
                        <span class="medicine-row__index">#{{ $index + 1 }}</span>
                        <button type="button"
                                wire:click="decrease({{ $index }})"
                                class="btn btn-sm btn-link text-danger p-0 ms-auto"
                                title="{{ trans('doctor::doctor.delete') ?? 'Remove' }}">
                            <i class="ti tabler-trash"></i>
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 col-xl-4">
                            <x-core::select
                                :label="trans('doctor::doctor.medicalExaminations.drugName')"
                                :placeholder="trans('doctor::doctor.medicalExaminations.drugName')"
                                id="medicinesData.{{ $index }}.medicine_id"
                                name="medicinesData.{{ $index }}.medicine_id"
                                model="medicinesData.{{ $index }}.medicine_id"
                                required="required"
                                :options="$medicines"
                                :value="$medicine['medicine_id'] ?? ''"/>
                        </div>

                        <div class="col-md-6 col-xl-3">
                            <x-core::select
                                :label="trans('doctor::doctor.medicalExaminations.howToDrink')"
                                :placeholder="trans('doctor::doctor.medicalExaminations.howToDrink')"
                                id="medicinesData.{{ $index }}.dosage_form_id"
                                name="medicinesData.{{ $index }}.dosage_form_id"
                                model="medicinesData.{{ $index }}.dosage_form_id"
                                required="required"
                                :options="$dosageForms"
                                :value="$medicine['dosage_form_id'] ?? ''"/>
                        </div>

                        <div class="col-md-4 col-xl-2">
                            <x-core::input
                                label="doctor::doctor.medicalExaminations.dose"
                                id="medicinesData.{{ $index }}.dose"
                                name="medicinesData.{{ $index }}.dose"
                                model="medicinesData.{{ $index }}.dose"
                                type="text"
                                required="required"
                                :value="$medicine['dose'] ?? ''"/>
                        </div>

                        <div class="col-md-4 col-xl-2">
                            <x-core::input
                                label="doctor::doctor.medicalExaminations.dosage"
                                id="medicinesData.{{ $index }}.dosage"
                                name="medicinesData.{{ $index }}.dosage"
                                model="medicinesData.{{ $index }}.dosage"
                                type="text"
                                required="required"
                                :value="$medicine['dosage'] ?? ''"/>
                        </div>

                        <div class="col-md-4 col-xl-1">
                            <x-core::input
                                label="doctor::doctor.medicalExaminations.duration"
                                id="medicinesData.{{ $index }}.duration"
                                name="medicinesData.{{ $index }}.duration"
                                model="medicinesData.{{ $index }}.duration"
                                type="text"
                                required="required"
                                :value="$medicine['duration'] ?? ''"/>
                        </div>

                        <div class="col-12">
                            <x-core::textarea
                                label="doctor::doctor.medicalExaminations.note"
                                required=""
                                id="medicinesData.{{ $index }}.note"
                                name="medicinesData.{{ $index }}.note"
                                model="medicinesData.{{ $index }}.note"
                                :value="$medicine['note'] ?? ''"
                                type="text"/>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Bottom save bar --}}
        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 pt-3 border-top">
            <button type="button" class="btn btn-outline-primary" wire:click="increase()">
                <i class="ti tabler-plus me-1"></i>
                {{ trans('doctor::doctor.medicalExaminations.drugName') }}
            </button>
            <button type="button" class="btn btn-primary" wire:click="save">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ trans('doctor::doctor.save') }}
            </button>
        </div>
    @endif
</div>

@push('scripts')
    <style>
        .medicines-block .medicine-row {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-left: 3px solid var(--bs-primary);
            border-radius: var(--bs-border-radius, 0.375rem);
            padding: 1rem 1rem .5rem;
            margin-bottom: 1rem;
        }
        .medicines-block .medicine-row__header {
            display: flex;
            align-items: center;
            margin-bottom: .75rem;
            padding-bottom: .5rem;
            border-bottom: 1px dashed var(--bs-border-color);
        }
        .medicines-block .medicine-row__index {
            font-size: .78rem;
            font-weight: 600;
            color: var(--bs-primary);
            text-transform: uppercase;
            letter-spacing: .05em;
        }
    </style>
@endpush
