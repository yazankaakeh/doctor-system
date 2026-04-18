<?php

$page = 'sales-dashboard'; ?>
@extends('theme::user.layouts.horizontalLayout')

<!-- Vendor Styles -->
@section('vendor-style')
    @livewireStyles
    @livewireScripts
    @vite(['resources/assets/vendor/libs/dropzone/dropzone.scss'],
            'build/modules/theme')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
            'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
            'resources/assets/vendor/libs/select2/select2.scss',
            'resources/assets/vendor/libs/@form-validation/form-validation.scss'],
            'build/modules/theme')
@endsection

<!-- Vendor Scripts -->
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
        })
    </script>
    @vite(['resources/assets/vendor/libs/dropzone/dropzone.js'],
'build/modules/theme')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js'],
'build/modules/theme')

@endsection

<!-- Page Scripts -->
@section('page-script')
    @includeIf('doctor::doctor.vitalSign.modals.createModal')
    @includeIf('doctor::doctor.vitalSign.modals.editModal')
    @vite(['resources/assets/js/forms-file-upload.js'],'build/modules/theme')
    {{--
      @vite(['resources/assets/js/form-wizard-numbered.js', 'resources/assets/js/form-wizard-validation.js'])
    --}}
@endsection
@section('title', trans('customer.sidebar.vitalSign'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between pb-2 mb-1">
                            <h5 class="">{{trans('customer.sidebar.vitalSign')}}</h5>
                            <h5 class="">
                                @can('doctor.vitalSign.store')
                                    <button type="button"
                                            data-bs-toggle="modal" data-bs-target="#storeModal"
                                            class="btn btn-primary">
                                        <i class="ti tabler-plus icon-base me-1"></i>
                                        {{trans('doctor::doctor.create')}}
                                    </button>
                                @endcan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="card-content">
                                <div class="table-responsive text-nowrap">
                                    <table class="table datanew">
                                        <thead>
                                        <tr>
                                            <th>{{trans('doctor::doctor.id')}}</th>
                                            <th>{{trans('doctor::doctor.vitalSign.name')}}</th>
                                            <th>{{trans('doctor::doctor.vitalSign.normal_range')}}</th>
                                            <th>{{trans('customer.account.status')}}</th>
                                            <th>{{trans('admin.audits.action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                        @foreach($data as $vitalSign)
                                            <tr>
                                                <td>{{$vitalSign->id}}</td>
                                                <td>{{$vitalSign->name}}</td>
                                                <td>{{$vitalSign->normal_range ?? '-'}}</td>
                                                <td>
                                                    <span class="badge text-bg-{{$vitalSign->is_active->class()}} me-1">{{$vitalSign->is_active->label()}} </span>
                                                </td>
                                                <td class="action-table-data">
                                                    <div class="edit-delete-action">
                                                        @can('doctor.vitalSign.update')
                                                            <a type="button" data-bs-toggle="modal"
                                                               data-bs-target="#editModal"
                                                               class="me-2 btn btn-outline-primary text-primary p-2 btn-sm EditModalBTN"
                                                               data-id="{{$vitalSign->id}}"
                                                               data-name='@json($vitalSign->getTranslations('name'))'
                                                               data-min-value="{{$vitalSign->min_value}}"
                                                               data-max-value="{{$vitalSign->max_value}}"
                                                               data-unit="{{$vitalSign->unit}}"
                                                               data-active="{{$vitalSign->is_active}}">
                                                                <i data-feather="edit"
                                                                   class="ti tabler-edit icon-base"></i>
                                                            </a>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    {{$data->links()}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

