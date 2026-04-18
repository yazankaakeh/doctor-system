<?php
$page = 'users-management';
?>
@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('adminmanagement::admin_management.user.title'))

<!-- Vendor Styles -->
@section('vendor-style')
    @livewireStyles
    @livewireScripts
    @vite([
        'resources/assets/vendor/libs/dropzone/dropzone.scss',
        'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss'
    ], 'build/modules/theme')
@endsection

<!-- Page Styles -->
@section('page-style')
    <style>
        .user-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .user-card .avatar {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
        }
        .user-card .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-info {
            min-width: 0;
        }
        .user-info .user-name {
            font-weight: 600;
            color: var(--bs-heading-color);
            margin-bottom: 0.125rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-info .user-email {
            font-size: 0.8125rem;
            color: var(--bs-secondary-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stats-card {
            border: none;
            border-radius: 0.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        .stats-card .stats-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-size: 1.5rem;
        }
        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
        }
        .table tbody tr {
            transition: background-color 0.2s;
        }
        .table tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.04);
        }
        .badge-role {
            font-weight: 500;
            padding: 0.5em 0.75em;
        }
        .search-box {
            max-width: 300px;
        }
        .empty-state {
            padding: 3rem;
            text-align: center;
        }
        .empty-state i {
            font-size: 4rem;
            color: var(--bs-secondary-color);
            margin-bottom: 1rem;
        }
    </style>
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/dropzone/dropzone.js',
        'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
        'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js'
    ], 'build/modules/theme')
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/forms-file-upload.js'], 'build/modules/theme')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Select2 inside modals
            $('.select2').each(function () {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal'),
                    allowClear: true,
                    tags: false
                });
            });

            // Search functionality
            const searchInput = document.getElementById('searchUsers');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    document.querySelectorAll('tbody tr').forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection

@section('content')
    @php
        $totalUsers = $users->total();
        $activeUsers = $users->where('is_active.value', 1)->count();
        $pendingUsers = $users->where('is_active.value', 0)->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">{{ trans('adminmanagement::admin_management.user.title') }}</h4>
                <p class="text-muted mb-0">{{ trans('adminmanagement::admin_management.user.subtitle') }}</p>
            </div>
            @can('admin.user_management.store')
                <button type="button" data-bs-toggle="modal" data-bs-target="#storeModal"
                        class="btn btn-primary">
                    <i class="ti tabler-plus me-1"></i>
                    {{ trans('adminmanagement::admin_management.user.add_new') }}
                </button>
            @endcan
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon bg-label-primary me-3">
                            <i class="ti tabler-users"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $totalUsers }}</h3>
                            <span class="text-muted">{{ trans('adminmanagement::admin_management.user.total_doctors') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon bg-label-success me-3">
                            <i class="ti tabler-user-check"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $activeUsers }}</h3>
                            <span class="text-muted">{{ trans('adminmanagement::admin_management.user.active_doctors') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card stats-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stats-icon bg-label-warning me-3">
                            <i class="ti tabler-clock"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $pendingUsers }}</h3>
                            <span class="text-muted">{{ trans('adminmanagement::admin_management.user.pending_doctors') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title mb-0">{{ trans('adminmanagement::admin_management.user.doctors') }}</h5>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Search -->
                        <div class="search-box">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text border-0"><i class="ti tabler-search"></i></span>
                                <input type="text" id="searchUsers" class="form-control border-0 bg-light"
                                       placeholder="{{ trans('adminmanagement::admin_management.user.search_placeholder') }}">
                            </div>
                        </div>
                        @if($pendingUsers > 0)
                            <span class="badge bg-warning rounded-pill">
                                {{ $pendingUsers }} {{ trans('adminmanagement::admin_management.user.pending_approval') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ trans('adminmanagement::admin_management.user.table.doctor') }}</th>
                                <th>{{ trans('adminmanagement::admin_management.user.table.role') }}</th>
                                <th>{{ trans('adminmanagement::admin_management.user.table.gender') }}</th>
                                <th>{{ trans('adminmanagement::admin_management.user.table.status') }}</th>
                                <th class="text-center pe-4">{{ trans('adminmanagement::admin_management.user.table.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <div class="user-card">
                                            <div class="avatar rounded-circle overflow-hidden">
                                                <img src="{{ $user->getFirstMediaUrl('images') ?: asset('assets/img/avatars/default.png') }}"
                                                     alt="{{ $user->name }}">
                                            </div>
                                            <div class="user-info">
                                                <div class="user-name">{{ $user->name }}</div>
                                                <div class="user-email">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary badge-role">
                                            <i class="ti tabler-shield me-1"></i>
                                            {{ $user->roles?->first()?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $user->gender->class() }}">
                                            <i class="ti tabler-{{ $user->gender->value == 'male' ? 'man' : 'woman' }} me-1"></i>
                                            {{ $user->gender->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->is_active->value == 1)
                                            <span class="badge bg-success">
                                                <i class="ti tabler-check me-1"></i>
                                                {{ $user->is_active->label() }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="ti tabler-clock me-1"></i>
                                                {{ $user->is_active->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        @if($user->id != 1)
                                            <div class="d-inline-flex gap-1">
                                                @can('admin.user_management.update')
                                                    <button type="button"
                                                            class="btn btn-icon btn-label-primary action-btn EditModalBTN"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal"
                                                            data-bs-tooltip="tooltip"
                                                            title="{{ trans('adminmanagement::admin_management.user.edit_doctor') }}"
                                                            data-id="{{ $user->id }}"
                                                            data-name="{{ $user->name }}"
                                                            data-email="{{ $user->email }}"
                                                            data-active="{{ $user->is_active->value }}"
                                                            data-img="{{ $user->getFirstMediaUrl('images') }}"
                                                            data-role="{{ $user->roles?->first()?->id }}">
                                                        <i class="ti tabler-edit fs-5"></i>
                                                    </button>
                                                @endcan

                                                @can('admin.user_management.status')
                                                    @if($user->is_active->value == 0)
                                                        <button type="button"
                                                                class="btn btn-icon btn-label-success action-btn IsActiveModalBTN"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#isActiveModal"
                                                                data-bs-tooltip="tooltip"
                                                                title="{{ trans('adminmanagement::admin_management.user.approve_doctor') }}"
                                                                data-id="{{ $user->id }}"
                                                                data-name="{{ $user->name }}"
                                                                data-email="{{ $user->email }}"
                                                                data-active="{{ $user->is_active->value }}">
                                                            <i class="ti tabler-check fs-5"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-icon btn-label-secondary action-btn IsActiveModalBTN"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#isActiveModal"
                                                                data-bs-tooltip="tooltip"
                                                                title="{{ trans('adminmanagement::admin_management.user.change_status') }}"
                                                                data-id="{{ $user->id }}"
                                                                data-name="{{ $user->name }}"
                                                                data-email="{{ $user->email }}"
                                                                data-active="{{ $user->is_active->value }}">
                                                            <i class="ti tabler-user-cog fs-5"></i>
                                                        </button>
                                                    @endif
                                                @endcan
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            {{ trans('adminmanagement::admin_management.user.showing') }}
                            {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }}
                            {{ trans('adminmanagement::admin_management.user.of') }}
                            {{ $users->total() }}
                        </span>
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="ti tabler-users-group d-block"></i>
                        <h5>{{ trans('adminmanagement::admin_management.user.no_doctors') }}</h5>
                        <p class="text-muted mb-0">{{ trans('adminmanagement::admin_management.user.no_doctors_desc') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @includeIf('adminmanagement::users.modals.createModal')
    @includeIf('adminmanagement::users.modals.editModal')
    @includeIf('adminmanagement::users.modals.isActiveModal')
@endsection
