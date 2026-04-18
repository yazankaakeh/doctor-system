@php
    $page = 'sales-dashboard';
@endphp
@extends('theme::user.layouts.horizontalLayout')

<!-- Vendor Styles -->
@section('vendor-style')
    @livewireStyles
    @livewireScripts
    @includeIf('doctor::doctor.finalDiagnosis.modals.createModal')
    @includeIf('doctor::doctor.medicalTest.modals.createModal')
    @includeIf('doctor::doctor.medicine.modals.createModal')
    @includeIf('doctor::doctor.medicalExamination.modals.uploadFileModal',['model'=>  $medicalExamination])

    @vite([
    'resources/assets/vendor/libs/dropzone/dropzone.scss',
    'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
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
    <script src="{{asset('livewire-select2/livewire-select2.js')}}"></script>
    @vite(['resources/assets/js/forms-file-upload.js'],'build/modules/theme')

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show notification
                if (typeof Toastify !== 'undefined') {
                    Toastify({
                        text: "{{ trans('core::video.link_copied') }}",
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        style: { background: "#28a745" }
                    }).showToast();
                } else {
                    alert("{{ trans('core::video.link_copied') }}");
                }
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>
@endsection

@section('title', trans('customer.sidebar.medicalExaminations'))

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <livewire:doctor::vital-signs-livewire :medicalExamination="$medicalExamination"/>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    @includeIf('doctor::doctor.medicalExamination.partials.patientCard',['patient'=> $patient])

                    {{-- Video Meeting & Chat Section --}}
                    @if(isset($booking) && $booking && $booking->isConfirmed())
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="ti tabler-video me-1"></i>
                                    {{ trans('core::video.video_consultation') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($booking->hasMeetingRoom())
                                    <a href="{{ route('video.join', ['roomName' => $booking->getMeetingRoomName(), 'booking' => $booking->id]) }}"
                                       class="btn btn-success w-100 mb-2"
                                       target="_blank">
                                        <i class="ti tabler-video me-1"></i>
                                        {{ trans('booking::booking.join_consultation') }}
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-success btn-sm w-100"
                                            onclick="copyToClipboard('{{ $booking->getMeetingLink() }}')">
                                        <i class="ti tabler-copy me-1"></i>
                                        {{ trans('core::video.copy_link') }}
                                    </button>
                                @else
                                    <p class="text-muted mb-0 small">{{ trans('booking::booking.no_meeting_scheduled') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Chat with Patient --}}
                        @if(class_exists('\Modules\Messaging\Services\ConversationService'))
                            <div class="mb-3">
                                @livewire('booking::booking-conversation', ['booking' => $booking, 'userType' => 'doctor'])
                            </div>
                        @endif
                    @endif

                    @includeIf('doctor::doctor.medicalExamination.partials.files',['model'=> $medicalExamination])
                    <div class="my-4">
                        <livewire:doctor::final-diagnosis-patient-livewire
                                :patientId="$patient->id"
                                name="finalDiagnose"
                                :medicalExamination="$medicalExamination"/>
                    </div>
                </div>
                <div class="col-lg-9">
                    @includeIf('doctor::doctor.medicalExamination.partials.medicalPreview',['medicalExamination'=>$medicalExamination])
                    <div class="row my-4">
                        <div class="col-6">
                            <livewire:doctor::medical-tests-livewire
                                    :medicalExamination="$medicalExamination"
                                    :title="trans('doctor::doctor.medicalExaminations.laboratoryTests')"
                                    :type="\Modules\Doctor\Enums\MedicalTestTypeEnum::LABORATORY_TESTS"
                                    name="laboratoryTests"/>
                        </div>
                        <div class="col-6">
                            <livewire:doctor::medical-tests-livewire
                                    :medicalExamination="$medicalExamination"
                                    :title="trans('doctor::doctor.medicalExaminations.radiologyTests')"
                                    :type="\Modules\Doctor\Enums\MedicalTestTypeEnum::RADIOLOGY_TESTS"
                                    name="radiologyTests"/>
                        </div>
                    </div>
                    <div class="row my-4">
                        <div class="col-12">
                            <livewire:doctor::medicines-info-livewire
                                    :medicalExamination="$medicalExamination"/>
                        </div>
                    </div>
                    <div class="row">
                        {{--<div class="col-12 my-4">
                            <livewire:doctor::final-diagnosis-patient-livewire
                                    :patientId="$patient->id"
                                    name="finalDiagnose"
                                    :medicalExamination="$medicalExamination"/>
                        </div>--}}
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach($medicalExaminations as $medicalExam)
                    <div class="col-6 my-3">
                        @includeIf('doctor::doctor.medicalExamination.partials.medicalExamination',['medicalExam'=>$medicalExam])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

