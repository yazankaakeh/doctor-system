<div class="vital-signs-block">
    {{-- Header bar: title + quick controls --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center">
            <i class="ti tabler-heartbeat text-primary me-2"></i>
            <h6 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.vitalSignsInfo') }}</h6>
            <small class="text-muted ms-2">
                ({{ $vitalSigns->count() }} {{ trans('doctor::doctor.medicalExaminations.vitalSignsInfo') }})
            </small>
        </div>
        <div class="d-flex gap-2">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    wire:click="toggle">
                <i class="ti tabler-{{ $isOpen ? 'chevron-up' : 'chevron-down' }} me-1"></i>
                {{ $isOpen ? trans('doctor::doctor.close') : trans('doctor::doctor.show') }}
            </button>
            <button type="button"
                    class="btn btn-sm btn-primary"
                    wire:click="saveAll">
                <i class="ti tabler-device-floppy me-1"></i>
                {{ trans('doctor::doctor.saveAll') }}
            </button>
        </div>
    </div>

    {{-- Vital signs grid --}}
    <div class="collapse {{ $isOpen ? 'show' : '' }}" id="vitalSignsGrid">
        @if($vitalSigns->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="ti tabler-heartbeat" style="font-size: 2rem;"></i>
                <div class="mt-2">{{ trans('doctor::doctor.medicalExaminations.vitalSignsInfo') }}</div>
            </div>
        @else
            <div class="row g-3">
                @foreach($vitalSigns as $vitalSign)
                    @php
                        $currentValue = $values[$vitalSign->id] ?? null;
                        $numericValue = is_numeric($currentValue) ? (float) $currentValue : null;
                        $isInRange = $vitalSign->isValueInRange($numericValue);

                        $stateClass = 'vital-card--empty';
                        $badgeClass = 'text-bg-secondary';
                        $badgeIcon  = 'minus';
                        $badgeLabel = trans('doctor::doctor.medicalExaminations.vitalSignsInfo');
                        if ($numericValue !== null && $isInRange === true) {
                            $stateClass = 'vital-card--ok';
                            $badgeClass = 'text-bg-success';
                            $badgeIcon  = 'check';
                            $badgeLabel = trans('doctor::doctor.vitalSign.normal_range');
                        } elseif ($numericValue !== null && $isInRange === false) {
                            $stateClass = 'vital-card--out';
                            $badgeClass = 'text-bg-danger';
                            $badgeIcon  = 'alert-triangle';
                            $badgeLabel = trans('doctor::doctor.vitalSign.out_of_range');
                        } elseif ($currentValue !== null && $currentValue !== '') {
                            $stateClass = 'vital-card--set';
                        }
                    @endphp
                    <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                        <div class="vital-card {{ $stateClass }}">
                            <div class="vital-card__head">
                                <span class="vital-card__name">
                                    {{ $vitalSign->name }}
                                    @if($vitalSign->unit)
                                        <small class="text-muted">({{ $vitalSign->unit }})</small>
                                    @endif
                                </span>
                                @if($numericValue !== null || ($currentValue !== null && $currentValue !== ''))
                                    <span class="badge {{ $badgeClass }}" title="{{ $badgeLabel }}">
                                        <i class="ti tabler-{{ $badgeIcon }}"></i>
                                    </span>
                                @endif
                            </div>

                            <div class="input-group input-group-sm vital-card__input">
                                <input type="text"
                                       id="vital-{{ $vitalSign->id }}"
                                       name="values[{{ $vitalSign->id }}]"
                                       wire:model.live.debounce.500ms="values.{{ $vitalSign->id }}"
                                       class="form-control @error('values.'.$vitalSign->id) is-invalid @enderror"
                                       placeholder="{{ $vitalSign->normal_range ?: trans('doctor::doctor.pleaseSelectOne') }}">
                                <button type="button"
                                        class="btn btn-primary"
                                        wire:click="saveOne({{ $vitalSign->id }})"
                                        wire:loading.attr="disabled"
                                        title="{{ trans('doctor::doctor.save') }}">
                                    <i class="ti tabler-device-floppy"></i>
                                </button>
                            </div>

                            @if($vitalSign->normal_range)
                                <small class="vital-card__range">
                                    <i class="ti tabler-ruler-measure me-1"></i>
                                    {{ trans('doctor::doctor.vitalSign.normal_range') }}: {{ $vitalSign->normal_range }}
                                </small>
                            @endif

                            @error('values.'.$vitalSign->id)
                                <small class="text-danger d-block mt-1">
                                    <i class="ti tabler-alert-triangle me-1"></i>{{ $message }}
                                </small>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <style>
        /* SweetAlert z-index fix (stays from earlier change) */
        .swal2-container { z-index: 10050 !important; }
        .swal2-toast     { min-width: 320px; max-width: 420px; }
        .swal2-popup     { font-size: 0.95rem; }
        .swal2-title     { line-height: 1.4; }

        /* ---------- Vital-signs card styling (neutral, state-aware) ---------- */
        .vital-signs-block .vital-card {
            background: var(--bs-card-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: var(--bs-border-radius, 0.375rem);
            padding: .75rem .85rem;
            transition: border-color .15s ease, box-shadow .15s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }
        .vital-signs-block .vital-card:hover { border-color: var(--bs-primary); }
        .vital-signs-block .vital-card--set { border-left: 3px solid var(--bs-primary); }
        .vital-signs-block .vital-card--ok  { border-left: 3px solid var(--bs-success); }
        .vital-signs-block .vital-card--out { border-left: 3px solid var(--bs-danger); background: rgba(var(--bs-danger-rgb), .035); }
        .vital-signs-block .vital-card__head {
            display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        }
        .vital-signs-block .vital-card__name {
            font-size: .9rem; font-weight: 600; line-height: 1.25;
            color: var(--bs-body-color);
        }
        .vital-signs-block .vital-card__name small { font-weight: 400; margin-left: .25rem; }
        .vital-signs-block .vital-card__range {
            color: var(--bs-secondary-color); font-size: .78rem;
        }
        .vital-signs-block .vital-card .badge {
            padding: .3rem .45rem; font-size: .7rem;
        }
        .vital-signs-block .vital-card__input .form-control { border-right: 0; }
    </style>
    <script>
        document.addEventListener('livewire:init', () => {
            window.addEventListener('toast', (e) => {
                const { type = 'success', message = '' } = e.detail || {};

                if (!window.Swal) {
                    console.log(`[${type}] ${message}`);
                    return;
                }

                const [head, ...rest] = String(message).split(/\.\s+/);
                const title = head + (rest.length ? '.' : '');
                const text  = rest.join('. ').trim();

                if (type === 'error' || type === 'warning') {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text:  text || undefined,
                        position: 'center',
                        showConfirmButton: true,
                        confirmButtonText: @json(__('OK')),
                        confirmButtonColor: type === 'error' ? '#e53e3e' : '#d69e2e',
                        timer: 4500,
                        timerProgressBar: true,
                        allowOutsideClick: true,
                        allowEscapeKey: true,
                        heightAuto: false,
                    });
                    return;
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: type,
                    title: title,
                    text:  text || undefined,
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });
            });
        });
    </script>
@endpush
