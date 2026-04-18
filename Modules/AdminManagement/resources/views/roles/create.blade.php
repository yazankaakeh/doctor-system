@extends('theme::user.layouts.horizontalLayout')
@section('title', trans('adminmanagement::admin_management.roles.create.title'))

@section('vendor-style')
<style>
    .permission-search {
        position: sticky;
        top: 0;
        z-index: 100;
        background: var(--bs-body-bg);
        padding: 1rem 0;
    }
    .permission-card {
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }
    .permission-card:hover {
        border-left-color: var(--bs-primary);
        box-shadow: 0 0.5rem 1rem rgba(var(--bs-body-color-rgb), 0.15);
    }
    .permission-count {
        font-size: 0.75rem;
        font-weight: 600;
    }
    .section-header {
        background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark, #0056b3) 100%);
        color: white;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    .permission-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--bs-border-color);
        transition: background-color 0.2s;
    }
    .permission-item:last-child {
        border-bottom: none;
    }
    .permission-item:hover {
        background-color: var(--bs-secondary-bg);
    }
    .select-all-badge {
        font-size: 0.875rem;
        cursor: pointer;
        user-select: none;
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">{{ trans('adminmanagement::admin_management.roles.create.title') }}</h2>
                        <p class="text-muted mb-0">{{ trans('adminmanagement::admin_management.roles.create.subtitle') }}</p>
                    </div>
                    <a href="{{ route('admin.role_management.index') }}" class="btn btn-outline-secondary">
                        <i class="icon-base ti tabler-arrow-left me-1"></i>
                        {{ trans('adminmanagement::admin_management.back') }}
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.role_management.store') }}" method="POST">
            @csrf
            @method('POST')

            <!-- Role Information Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="icon-base ti tabler-info-circle me-2"></i>
                                {{ trans('adminmanagement::admin_management.roles.create.role_info') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-core::input
                                        label="adminmanagement::admin_management.roles.create.name"
                                        placeholder="adminmanagement::admin_management.roles.create.name_placeholder"
                                        id="name"
                                        name="name"
                                        type="text"
                                        required="required"
                                        model="name"
                                        value="{{ old('name') }}">
                                    </x-core::input>
                                </div>
                                <div class="col-md-6">
                                    <x-core::input
                                        label="adminmanagement::admin_management.roles.create.guard"
                                        placeholder="adminmanagement::admin_management.roles.create.guard"
                                        id="guard"
                                        name="guard"
                                        type="text"
                                        required="required"
                                        disabled="disabled"
                                        model="guard"
                                        value="doctor">
                                    </x-core::input>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="icon-base ti tabler-shield-check me-2"></i>
                                    {{ trans('adminmanagement::admin_management.roles.create.permissions') }}
                                </h5>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-primary-subtle text-primary" id="selected-count">
                                        <i class="icon-base ti tabler-check me-1"></i>
                                        <span id="count-selected">0</span> {{ trans('adminmanagement::admin_management.roles.create.selected') }}
                                    </span>
                                    <div class="form-check form-switch mb-0">
                                        <input type="checkbox" id="allCheckBoxes" class="form-check-input" role="switch">
                                        <label class="form-check-label" for="allCheckBoxes">
                                            {{ trans('adminmanagement::admin_management.roles.create.select_all') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search Box -->
                            <div class="permission-search mb-4">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="icon-base ti tabler-search"></i>
                                            </span>
                                            <input type="text"
                                                   id="permission-search"
                                                   class="form-control"
                                                   placeholder="{{ trans('adminmanagement::admin_management.roles.create.search_permissions') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="section-filter" class="form-select">
                                            <option value="">{{ trans('adminmanagement::admin_management.roles.create.all_sections') }}</option>
                                            @foreach($permissions as $index => $permission)
                                                <option value="{{ $index }}">{{ trans('adminmanagement::admin_management.sections.'.$index) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions Grid -->
                            <div class="row" id="permissions-container">
                                @include('adminmanagement::roles.partial._permission')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.role_management.index') }}" class="btn btn-outline-secondary">
                            <i class="icon-base ti tabler-x me-1"></i>
                            {{ trans('adminmanagement::admin_management.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-check me-1"></i>
                            {{ trans('adminmanagement::admin_management.roles.create.submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        // Update selected count
        function updateSelectedCount() {
            const count = $('input[name^="permissions"]:checked').length;
            $('#count-selected').text(count);
        }

        // Search functionality
        $('#permission-search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();

            $('.permission-card').each(function() {
                const cardText = $(this).text().toLowerCase();
                if (cardText.includes(searchTerm)) {
                    $(this).closest('.col-md-4').show();
                } else {
                    $(this).closest('.col-md-4').hide();
                }
            });
        });

        // Section filter
        $('#section-filter').on('change', function() {
            const section = $(this).val();

            if (section === '') {
                $('.col-md-4').show();
            } else {
                $('.col-md-4').hide();
                $('.' + section).closest('.col-md-4').show();
            }
        });

        // Update count on checkbox change
        $('input[type="checkbox"]').on('change', updateSelectedCount);

        // Initial count
        updateSelectedCount();
    });
</script>
@endsection
