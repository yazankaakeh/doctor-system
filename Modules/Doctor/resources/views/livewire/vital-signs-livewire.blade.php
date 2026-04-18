<div>
    <div class="demo-inline-spacing mb-5" wire:ignore>
        <button class="btn btn-primary" type="button"
                data-bs-toggle="modal" data-bs-target="#uploadFile">
            <i class="me-2 ti icon-base tabler-upload"></i>
            {{trans('doctor::doctor.medicalExaminations.uploadFile')}}
        </button>
        <a wire:click="toggle" class="btn btn-primary me-1" data-bs-toggle="collapse" href="#collapseExample"
           role="button">
            <i class="me-2 ti icon-base tabler-switch-vertical"></i>
            {{trans('doctor::doctor.medicalExaminations.vitalSignsInfo')}}
        </a>
        <span class="dropdown">
        <button class="btn btn-info border-0 mt-4 waves-effect"
                type="button" id="teamMemberList" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-printer icon-22px"></i>
            {{trans('doctor::doctor.medicalExaminations.printMedicalTests')}}
        </button>
        <div class="dropdown-menu dropdown-menu-center" aria-labelledby="teamMemberList" style="">
            <a href="{{route('doctor.pdf.downloadMedicalTest',['id'=> $medicalExamination->id, 'pageSize' => 'A5'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A5
            </a>
            <a href="{{route('doctor.pdf.downloadMedicalTest',['id'=> $medicalExamination->id, 'pageSize' => 'A4'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A4
            </a>
            <a href="{{route('doctor.pdf.downloadMedicalTest',['id'=> $medicalExamination->id, 'pageSize' => 'A3'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A3
            </a>
        </div>
        </span>

        <span class="dropdown">
        <button class="btn btn-success  border-0 mt-4 waves-effect"
                type="button" id="teamMemberList" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-printer icon-22px"></i>
            {{trans('doctor::doctor.medicalExaminations.printMedicines')}}
        </button>
        <div class="dropdown-menu dropdown-menu-center" aria-labelledby="teamMemberList" style="">
            <a href="{{route('doctor.pdf.downloadMedicines',['id'=> $medicalExamination->id, 'pageSize' => 'A5'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A5
            </a>
            <a href="{{route('doctor.pdf.downloadMedicines',['id'=> $medicalExamination->id, 'pageSize' => 'A4'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A4
            </a>
            <a href="{{route('doctor.pdf.downloadMedicines',['id'=> $medicalExamination->id, 'pageSize' => 'A3'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A3
            </a>
        </div>
        </span>
        <span class="dropdown">
        <button class="btn btn-warning  border-0 mt-4 waves-effect"
                type="button" id="teamMemberList" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base ti tabler-printer icon-22px"></i>
            {{trans('doctor::doctor.medicalExaminations.printMedicinesPharmacy')}}
        </button>
        <div class="dropdown-menu dropdown-menu-center" aria-labelledby="teamMemberList" style="">
            <a href="{{route('doctor.pdf.downloadMedicinesPharmacy',['id'=> $medicalExamination->id, 'pageSize' => 'A5'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A5
            </a>
            <a href="{{route('doctor.pdf.downloadMedicinesPharmacy',['id'=> $medicalExamination->id, 'pageSize' => 'A4'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A4
            </a>
            <a href="{{route('doctor.pdf.downloadMedicinesPharmacy',['id'=> $medicalExamination->id, 'pageSize' => 'A3'])}}"
               target="_blank" class="dropdown-item waves-effect">
                <i data-feather="edit" class="ti tabler-file-type-pdf icon-base"></i>
                A3
            </a>
        </div>
        </span>
    </div>
    <div class="collapse {{ $isOpen ? 'show' : '' }}" id="collapseExample">
        <div class="row mb-5">
            @foreach($vitalSigns as $vitalSign)
                @php
                    $currentValue = $values[$vitalSign->id] ?? null;
                    $numericValue = is_numeric($currentValue) ? (float) $currentValue : null;
                    $isInRange = $vitalSign->isValueInRange($numericValue);
                    $borderClass = '';
                    if ($numericValue !== null && $isInRange !== null) {
                        $borderClass = $isInRange ? 'border-success' : 'border-danger';
                    }
                @endphp
                <div class="col-2 my-2">
                    <div class="card {{ $borderClass }}">
                        <div class="card-body">
                            <div class="card-content">
                                <x-core::input
                                        :label="$vitalSign->name . ($vitalSign->unit ? ' (' . $vitalSign->unit . ')' : '')"
                                        :id="'vital-'.$vitalSign->id"
                                        :name="'values['.$vitalSign->id.']'"
                                        type="text"
                                        model="values.{{ $vitalSign->id }}"
                                        :placeholder="$vitalSign->normal_range ?? ''"
                                />
                                @if($vitalSign->normal_range)
                                    <small class="text-muted d-block mt-1">
                                        {{ trans('doctor::doctor.vitalSign.normal_range') }}: {{ $vitalSign->normal_range }}
                                    </small>
                                @endif
                                @if($numericValue !== null && $isInRange === false)
                                    <small class="text-danger d-block mt-1">
                                        <i class="ti tabler-alert-triangle"></i>
                                        {{ trans('doctor::doctor.vitalSign.out_of_range') }}
                                    </small>
                                @endif
                                <div class="text-end mt-3">
                                    <button
                                            class="btn btn-sm btn-primary"
                                            wire:click="saveOne({{ $vitalSign->id }})"
                                            type="button">
                                        {{ trans('doctor::doctor.save') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="text-end">
                <button class="btn btn-primary" wire:click="saveAll" type="button">
                    {{ trans('doctor::doctor.saveAll') }}
                </button>
            </div>
        </div>

    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            window.addEventListener('toast', (e) => {
                const {type = 'success', message = ''} = e.detail || {};
                // SweetAlert2 toast
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: type,
                        title: message,
                        showConfirmButton: false,
                        timer: 0,
                        timerProgressBar: true,
                    });
                } else {
                    // Fallback
                    console.log(`[${type}] ${message}`);
                }
            });
        });
    </script>
@endpush