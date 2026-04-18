<?php
$page = 'sales-dashboard'; ?>
@extends('theme::user.layouts.horizontalLayout')

{{-- Vendor Styles --}}
@section('vendor-style')
    @livewireStyles
    @livewireScripts
    @vite(['resources/assets/vendor/libs/select2/select2.scss',
            'resources/assets/vendor/libs/@form-validation/form-validation.scss'],
            'build/modules/theme')
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('.select2').each(function () {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal'),
                    allowClear: true,
                    tags: false
                });
            });
        });
    </script>
    @vite(['resources/assets/vendor/libs/select2/select2.js',
            'resources/assets/vendor/libs/@form-validation/popular.js',
            'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
            'resources/assets/vendor/libs/@form-validation/auto-focus.js'],
            'build/modules/theme')
@endsection

{{-- Page Scripts --}}
@section('page-script')
    @includeIf('doctor::doctor.medicalExamination.modals.createModal')
    @includeIf('doctor::doctor.medicalExamination.modals.filterModal')
@endsection

@section('title', trans('doctor::doctor.medicalExaminations.title'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between pb-2 mb-1">
                            <h5 class="mb-0">{{ trans('doctor::doctor.medicalExaminations.title') }}</h5>
                            <div class="d-flex gap-2">
                                @can('doctor.medicalExamination.store')
                                    <button type="button"
                                            data-bs-toggle="modal" data-bs-target="#storeModal"
                                            class="btn btn-primary">
                                        <i class="ti tabler-plus icon-base me-1"></i>
                                        {{ trans('doctor::doctor.create') }}
                                    </button>
                                @endcan
                                <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#filterModal"
                                        class="btn btn-success">
                                    <i class="ti tabler-filter icon-base me-1"></i>
                                    {{ trans('doctor::doctor.filter') }}
                                </button>
                            </div>
                        </div>

                        {{-- Active filter chips --}}
                        @if(array_filter($filters ?? []))
                            <div class="card-body pb-0">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <small class="text-muted me-2">
                                        {{ trans('doctor::doctor.medicalExaminations.activeFilters') }}:
                                    </small>
                                    @if(!empty($filters['patient_id']) && $patients->has($filters['patient_id']))
                                        <span class="badge bg-label-primary">
                                            {{ trans('doctor::doctor.patients.name') }}:
                                            {{ $patients[$filters['patient_id']] }}
                                        </span>
                                    @endif
                                    @if(!empty($filters['status']))
                                        @php $statusEnum = \Modules\Doctor\Enums\MedicalExaminationStatusEnum::tryFrom((int) $filters['status']); @endphp
                                        @if($statusEnum)
                                            <span class="badge bg-label-{{ $statusEnum->class() }}">
                                                {{ trans('customer.account.status') }}: {{ $statusEnum->label() }}
                                            </span>
                                        @endif
                                    @endif
                                    @if(!empty($filters['clinic_id']) && $clinics->has($filters['clinic_id']))
                                        <span class="badge bg-label-info">
                                            {{ trans('customer.sidebar.clinic') }}:
                                            {{ $clinics[$filters['clinic_id']] }}
                                        </span>
                                    @endif
                                    @if(!empty($filters['from']))
                                        <span class="badge bg-label-secondary">
                                            {{ trans('doctor::doctor.medicalExaminations.from') }}: {{ $filters['from'] }}
                                        </span>
                                    @endif
                                    @if(!empty($filters['to']))
                                        <span class="badge bg-label-secondary">
                                            {{ trans('doctor::doctor.medicalExaminations.to') }}: {{ $filters['to'] }}
                                        </span>
                                    @endif
                                    <a href="{{ route('doctor.medicalExamination.index') }}"
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="ti tabler-x me-1"></i>
                                        {{ trans('doctor::doctor.medicalExaminations.clearFilters') }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="card-body">
                            <div class="card-content">
                                <div class="table-responsive text-nowrap">
                                    <table class="table datanew">
                                        <thead>
                                        <tr>
                                            <th>{{ trans('doctor::doctor.id') }}</th>
                                            <th>{{ trans('doctor::doctor.patients.name') }}</th>
                                            <th>{{ trans('customer.sidebar.clinic') }}</th>
                                            <th>{{ trans('doctor::doctor.medicalExaminations.reasonOfVisiting') }}</th>
                                            <th>{{ trans('customer.account.status') }}</th>
                                            <th>{{ trans('doctor::doctor.medicalExaminations.createdAt') }}</th>
                                            <th>{{ trans('admin.audits.action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                        @forelse($data as $medicalExamination)
                                            <tr>
                                                <td>#{{ $medicalExamination->id }}</td>
                                                <td>
                                                    @if($medicalExamination->patient)
                                                        <div class="d-flex align-items-center">
                                                            <img
                                                                src="{{ $medicalExamination->patient->getFirstMediaUrl('images') ?: asset('assets/img/avatars/3.png') }}"
                                                                alt="{{ $medicalExamination->patient->name }}"
                                                                class="rounded-circle me-2"
                                                                style="width: 32px; height: 32px; object-fit: cover;">
                                                            <span>{{ $medicalExamination->patient->name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $medicalExamination->clinic?->name ?? '-' }}</td>
                                                <td>
                                                    <span class="text-muted">
                                                        {{ \Illuminate\Support\Str::limit($medicalExamination->reason_of_visiting ?? '-', 40) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($medicalExamination->status)
                                                        <span class="badge text-bg-{{ $medicalExamination->status->class() }}">
                                                            {{ $medicalExamination->status->label() }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $medicalExamination->created_at?->format('Y-m-d H:i') }}</td>
                                                <td class="action-table-data">
                                                    <div class="dropdown">
                                                        <button class="btn btn-text-secondary btn-icon rounded-pill border-0"
                                                                type="button"
                                                                data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                            <i class="icon-base ti tabler-dots-vertical icon-22px"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            @can('doctor.medicalExamination.create')
                                                                <a href="{{ route('doctor.medicalExamination.create', $medicalExamination->id) }}"
                                                                   class="dropdown-item waves-effect">
                                                                    <i class="ti tabler-edit icon-base me-1"></i>
                                                                    {{ trans('doctor::doctor.edit') }}
                                                                </a>
                                                            @endcan
                                                            @can('doctor.medicalExamination.show')
                                                                <a href="{{ route('doctor.medicalExamination.show', $medicalExamination->id) }}"
                                                                   class="dropdown-item waves-effect">
                                                                    <i class="ti tabler-eye icon-base me-1"></i>
                                                                    {{ trans('doctor::doctor.show') }}
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    <i class="ti tabler-folder-off icon-base mb-2" style="font-size: 2rem;"></i>
                                                    <div>{{ trans('doctor::doctor.medicalExaminations.empty') }}</div>
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                    {{ $data->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
