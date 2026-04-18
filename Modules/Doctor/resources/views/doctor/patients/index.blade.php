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
    @includeIf('doctor::doctor.patients.modals.createModal')
    @includeIf('doctor::doctor.patients.modals.editModal')
    @includeIf('doctor::doctor.patients.modals.filterModal')
    @vite(['resources/assets/js/forms-file-upload.js'],'build/modules/theme')
    {{--
      @vite(['resources/assets/js/form-wizard-numbered.js', 'resources/assets/js/form-wizard-validation.js'])
    --}}
@endsection
@section('title', trans('customer.sidebar.patients'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between pb-2 mb-1">
                            <h5 class="">{{trans('customer.sidebar.patients')}}</h5>
                            <h5 class="">
                                @can('doctor.patients.store')
                                    <button type="button"
                                            data-bs-toggle="modal" data-bs-target="#storeModal"
                                            class="btn btn-primary">
                                        <i class="ti tabler-plus icon-base me-1"></i>
                                        {{trans('doctor::doctor.create')}}
                                    </button>
                                @endcan
                                <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#filterModal"
                                        class="btn btn-success">
                                    <i class="ti tabler-plus icon-base me-1"></i>
                                    {{trans('doctor::doctor.filter')}}
                                </button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="card-content">
                                <div class="table-responsive text-nowrap">
                                    <table class="table datanew">
                                        <thead>
                                        <tr>
                                            {{--<th>{{trans('doctor::doctor.id')}}</th>--}}
                                            <th>{{trans('doctor::doctor.patients.name')}}</th>
                                            <th>{{trans('doctor::doctor.patients.phone')}}</th>
                                            <th>{{trans('doctor::doctor.patients.age')}}</th>
                                            <th>{{trans('doctor::doctor.patients.gender')}}</th>
                                            <th>{{trans('doctor::doctor.patients.children')}}</th>
                                            {{--<th>{{trans('doctor::doctor.patients.work')}}</th>--}}
                                            <th>{{trans('doctor::doctor.patients.blood_type')}}</th>
                                            <th>{{trans('doctor::doctor.patients.marital_status')}}</th>
                                            {{--<th>{{trans('doctor::doctor.patients.drug_allergies')}}</th>
                                            <th>{{trans('doctor::doctor.patients.disabilities')}}</th>
                                            <th>{{trans('doctor::doctor.patients.medical_history')}}</th>
                                            <th>{{trans('doctor::doctor.patients.surgical_history')}}</th>
                                            <th>{{trans('doctor::doctor.patients.accident_history')}}</th>--}}
                                            <th>{{trans('doctor::doctor.clinic.img')}}</th>
                                            {{--<th>{{trans('customer.account.status')}}</th>--}}
                                            <th>{{trans('admin.audits.action')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                        @foreach($data as $patient)
                                            <tr>
                                                {{--<td>{{$patient->id}}</td>--}}
                                                <td>{{$patient->name}}</td>
                                                <td>{{$patient->phone}}</td>
                                                <td>{{$patient->age}}</td>
                                                <td>
                                                    <span
                                                        class="badge text-bg-{{$patient?->gender?->class()}} me-1">{{$patient?->gender?->label()}} </span>
                                                </td>
                                                <td>{{$patient->children}}</td>
                                                {{--<td>{{$patient->work}}</td>--}}
                                                <td>
                                                    <span
                                                        class="badge text-bg-{{$patient?->blood_type?->class()}} me-1">{{$patient?->blood_type?->label()}} </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge text-bg-{{$patient?->marital_status?->class()}} me-1">{{$patient?->marital_status?->label()}} </span>
                                                </td>
                                                {{--<td>{{$patient->drug_allergies}}</td>
                                                <td>{{$patient->disabilities}}</td>
                                                <td>{{$patient->medical_history}}</td>
                                                <td>{{$patient->surgical_history}}</td>
                                                <td>{{$patient->accident_history}}</td>--}}
                                                <td>
                                                    <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
                                                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                            data-bs-placement="top"
                                                            class="avatar avatar-xl pull-up"
                                                            aria-label="{{$patient->name}}"
                                                            data-bs-original-title="{{$patient->name}}">
                                                            <img
                                                                src="{{$patient->getFirstMediaUrl('images') != null ? $patient->getFirstMediaUrl('images'): asset('assets/img/avatars/3.png') }}"
                                                                alt="Avatar"
                                                                class="rounded-circle">
                                                        </li>
                                                    </ul>
                                                </td>
                                                {{--<td>
                                                    <span class="badge text-bg-{{$patient->is_active->class()}} me-1">
                                                        {{$patient->is_active->label()}}
                                                    </span>
                                                </td>--}}
                                                <td class="action-table-data">
                                                    <div class="dropdown">
                                                        <button
                                                            class="btn btn-text-secondary btn-icon rounded-pill text-body-secondary border-0 me-n1 waves-effect"
                                                            type="button" id="teamMemberList"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="icon-base ti tabler-dots-vertical icon-22px text-body-secondary"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end"
                                                             aria-labelledby="teamMemberList" style="">
                                                            @can('doctor.patients.update')
                                                                <a type="button" data-bs-toggle="modal"
                                                                   data-bs-target="#editModal"
                                                                   class="dropdown-item EditModalBTN waves-effect"
                                                                   data-id="{{$patient->id}}"
                                                                   data-phone="{{$patient->phone}}"
                                                                   data-img="{{$patient->getFirstMediaUrl('images')}}"
                                                                   data-nationalityid='{{$patient->nationality_id}}'
                                                                   data-clinics='@json($patient?->clinics->pluck("id"))'
                                                                   data-name='{{$patient->name}}'
                                                                   data-age='{{$patient->age}}'
                                                                   data-gender='{{$patient?->gender?->value}}'
                                                                   data-marital-status='{{$patient?->marital_status?->value}}'
                                                                   data-children='{{$patient->children}}'
                                                                   data-work='{{$patient->work}}'
                                                                   data-drug-allergies='{{$patient->drug_allergies}}'
                                                                   data-blood-type='{{$patient?->blood_type?->value}}'
                                                                   data-disabilities='{{$patient->disabilities}}'
                                                                   data-medical-history='{{$patient->medical_history}}'
                                                                   data-surgical-history='{{$patient->surgical_history}}'
                                                                   data-accident-history='{{$patient->accident_history}}'
                                                                   data-email='{{$patient->email}}'
                                                                   data-active="{{$patient->is_active->value}}">
                                                                    <i data-feather="edit"
                                                                       class="ti tabler-edit icon-base"></i>
                                                                    {{trans('doctor::doctor.edit')}}
                                                                </a>
                                                            @endcan
                                                            @can('doctor.patients.show')
                                                                <a type="button"
                                                                   href="{{route('doctor.patients.show',['id'=>$patient->id])}}"
                                                                   class="dropdown-item waves-effect">
                                                                    <i data-feather="show"
                                                                       class="ti tabler-eye icon-base"></i>
                                                                    {{trans('doctor::doctor.show')}}
                                                                </a>
                                                            @endcan
                                                            @can('doctor.medicalExamination.store')
                                                                <form
                                                                    action="{{route('doctor.medicalExamination.store',['patientId'=>$patient->id])}}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('POST')
                                                                    <button type="submit"
                                                                            class="dropdown-item waves-effect">
                                                                        <i class="ti tabler-clipboard-heart icon-base"></i>
                                                                        {{trans('doctor::doctor.patients.createMedicalExamination')}}
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                    <div class="edit-delete-action">


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
