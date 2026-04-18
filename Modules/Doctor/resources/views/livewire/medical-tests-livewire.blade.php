<div data-livewire="{{$componentName}}" x-data="{ search: '', get visibleCount() { return this.$refs.testsGrid ? this.$refs.testsGrid.querySelectorAll('[x-show]:not([style*=\'display: none\'])').length : 0 } }">
    <style>
        .medical-test-card {
            transition: all 0.2s ease-in-out;
            border: 2px solid transparent;
        }
        .medical-test-card:hover {
            border-color: var(--bs-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.15);
        }
        .medical-test-card.selected {
            border-color: var(--bs-primary) !important;
            background-color: rgba(var(--bs-primary-rgb), 0.08);
        }
        .test-badge {
            transition: all 0.2s ease;
        }
        .test-badge:hover {
            transform: scale(1.02);
        }
        .test-badge .remove-btn {
            opacity: 0.7;
            transition: all 0.2s ease;
        }
        .test-badge:hover .remove-btn {
            opacity: 1;
            background-color: var(--bs-danger) !important;
        }
    </style>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar avatar-sm bg-label-primary rounded">
                    <span class="avatar-initial">
                        <i class="ti tabler-test-pipe icon-sm"></i>
                    </span>
                </div>
                <h5 class="mb-0 fw-semibold">{{$title}}</h5>
                @if(count($addedMedicalTests) > 0)
                    <span class="badge bg-primary rounded-pill">{{ count($addedMedicalTests) }}</span>
                @endif
            </div>
            <button data-bs-toggle="modal" data-bs-target="#storeModalMedicalTest"
                    class="btn btn-icon btn-sm rounded-circle btn-primary">
                <i class="ti tabler-plus fs-6"></i>
            </button>
        </div>
        <div class="card-body pt-2">
            @if(count($addedMedicalTests) > 0)
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($addedMedicalTests as $index => $medicalTest)
                        @php
                            $hasFile = $medicalTest->pivot && $medicalTest->pivot->getFirstMedia('attachment');
                            $fileUrl = $hasFile ? route('secure-file.download', ['mediaId' => $medicalTest->pivot->getFirstMedia('attachment')->id]) : null;
                        @endphp
                        <div class="test-badge badge bg-label-primary d-flex align-items-center gap-2 py-2 px-3 rounded-pill position-relative"
                             style="font-size: 0.875rem;">
                            <i class="ti tabler-flask fs-6"></i>
                            @if($hasFile)
                                <a href="{{ $fileUrl }}" target="_blank" class="text-decoration-none text-reset d-flex align-items-center gap-2" title="{{trans('doctor::doctor.viewFile')}}">
                                    <span>{{$medicalTest->name}}</span>
                                    <i class="ti tabler-file-text text-success fs-6"></i>
                                </a>
                            @else
                                <span>{{$medicalTest->name}}</span>
                            @endif
                            <button type="button"
                                    wire:click="removeTest({{$medicalTest->id}})"
                                    class="remove-btn btn btn-icon btn-xs rounded-circle bg-primary text-white p-0 d-flex align-items-center justify-content-center"
                                    style="width: 18px; height: 18px; min-width: 18px;"
                                    title="{{trans('doctor::doctor.delete')}}">
                                <i class="ti tabler-x" style="font-size: 10px;"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <div class="avatar avatar-lg bg-label-secondary rounded-circle mb-3">
                        <span class="avatar-initial">
                            <i class="ti tabler-flask-off icon-lg"></i>
                        </span>
                    </div>
                    <p class="text-muted mb-0">{{trans('doctor::doctor.medicalExaminations.noTestsAdded')}}</p>
                </div>
            @endif

            <button data-bs-toggle="modal" data-bs-target="#{{$name}}"
                    class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="ti tabler-plus"></i>
                {{trans('doctor::doctor.medicalExaminations.addMedicalTest')}}
            </button>
        </div>
    </div>

    {{-- Medical Tests Selection Modal --}}
    <div class="modal fade" wire:ignore.self id="{{$name}}" data-bs-backdrop="static" data-bs-keyboard="false"
         tabindex="-1" aria-labelledby="{{$name}}Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm bg-label-primary rounded">
                            <span class="avatar-initial">
                                <i class="ti tabler-test-pipe"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="{{$name}}Label">{{$title}}</h5>
                            <small class="text-muted">{{trans('doctor::doctor.medicalExaminations.selectTests')}}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    {{-- Search Box --}}
                    <div class="p-3 border-bottom bg-light sticky-top">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text bg-white"><i class="ti tabler-search"></i></span>
                            <input type="text"
                                   x-model="search"
                                   class="form-control"
                                   placeholder="{{trans('doctor::doctor.medicalExaminations.searchTests')}}">
                            <button type="button"
                                    x-show="search.length > 0"
                                    @click="search = ''"
                                    class="input-group-text bg-white cursor-pointer">
                                <i class="ti tabler-x"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                <span class="fw-semibold text-primary">{{ count($listMedicalTests) }}</span>
                                {{trans('doctor::doctor.medicalExaminations.testsSelected')}}
                            </small>
                            @if(count($listMedicalTests) > 0)
                                <button type="button"
                                        wire:click="$set('listMedicalTests', [])"
                                        class="btn btn-xs btn-outline-danger">
                                    <i class="ti tabler-trash me-1"></i>
                                    {{trans('doctor::doctor.clearAll')}}
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Tests Grid --}}
                    <div class="p-3">
                        <div class="row g-3" x-ref="testsGrid">
                            @foreach($medicalTests as $index => $medicalTest)
                                @php
                                    $checked = in_array((string)$index, $listMedicalTests);
                                @endphp
                                <div class="col-lg-4 col-md-6 col-sm-12 test-item"
                                     x-show="'{{ strtolower(addslashes($medicalTest)) }}'.includes(search.toLowerCase()) || search === ''"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100">
                                    <label for="listMedicalTests_{{$index}}"
                                           class="medical-test-card card h-100 mb-0 {{ $checked ? 'selected' : '' }}"
                                           style="cursor: pointer;">
                                        <div class="card-body p-3 d-flex align-items-center gap-3">
                                            <div class="form-check form-check-custom mb-0">
                                                <input class="form-check-input rounded"
                                                       type="checkbox"
                                                       wire:model.live="listMedicalTests"
                                                       id="listMedicalTests_{{$index}}"
                                                       value="{{$index}}"
                                                       style="width: 20px; height: 20px;">
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-medium {{ $checked ? 'text-primary' : '' }}">
                                                    {{$medicalTest}}
                                                </span>
                                            </div>
                                            @if($checked)
                                                <span class="badge bg-primary rounded-pill">
                                                    <i class="ti tabler-check" style="font-size: 10px;"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Empty Search State --}}
                        <template x-if="search.length > 0 && !Array.from($refs.testsGrid?.querySelectorAll('.test-item') || []).some(el => el.style.display !== 'none')">
                            <div class="text-center py-5">
                                <i class="ti tabler-search-off text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">{{trans('doctor::doctor.medicalExaminations.noTestsFound')}}</p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ti tabler-x me-1"></i>
                        {{trans('doctor::doctor.cancel')}}
                    </button>
                    <button type="button" wire:click="submit()" data-bs-dismiss="modal" class="btn btn-primary">
                        <i class="ti tabler-check me-1"></i>
                        {{trans('doctor::doctor.save')}}
                        @if(count($listMedicalTests) > 0)
                            <span class="badge bg-white text-primary ms-1">{{ count($listMedicalTests) }}</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
