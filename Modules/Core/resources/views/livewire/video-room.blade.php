<div class="video-room-container">
    {{-- Loading State --}}
    @if($isLoading)
        <div class="video-loading d-flex flex-column align-items-center justify-content-center" style="min-height: 500px;">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">{{ trans('core::video.loading') }}</span>
            </div>
            <p class="text-muted">{{ trans('core::video.preparing_room') }}</p>
        </div>
    @endif

    {{-- Error State --}}
    @if($hasError)
        <div class="video-error">
            <div class="card border-danger">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <span class="badge bg-danger rounded-pill p-3">
                            <i class="ti tabler-alert-triangle" style="font-size: 2rem;"></i>
                        </span>
                    </div>
                    <h4 class="text-danger mb-3">{{ trans('core::video.error_title') }}</h4>
                    <p class="text-muted mb-4">{{ $errorMessage ?? trans('core::video.error_generic') }}</p>
                    <button wire:click="$set('hasError', false)" class="btn btn-outline-primary">
                        <i class="ti tabler-refresh me-1"></i>
                        {{ trans('core::video.try_again') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Pre-Join Screen --}}
    @if($showPreJoinScreen && !$isLoading && !$hasError)
        <div class="pre-join-screen">
            <div class="card shadow-lg border-0">
                <div class="card-body p-0">
                    <div class="row g-0">
                        {{-- Left Side - Video Preview --}}
                        <div class="col-lg-7">
                            <div class="video-preview-container bg-dark position-relative" style="min-height: 400px;">
                                {{-- Camera Preview Placeholder --}}
                                <div class="video-preview d-flex flex-column align-items-center justify-content-center h-100 text-white">
                                    @if($cameraEnabled)
                                        <div id="local-preview" class="w-100 h-100 position-absolute top-0 start-0"></div>
                                        <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(transparent, rgba(0,0,0,0.7));">
                                            <span class="badge bg-success">
                                                <i class="ti tabler-video me-1"></i>
                                                {{ trans('core::video.camera_on') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <div class="avatar-preview mb-3 mx-auto bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                                @if($participantAvatar)
                                                    <img src="{{ $participantAvatar }}" alt="{{ $participantName }}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <span class="display-4 text-white">{{ strtoupper(substr($participantName, 0, 2)) }}</span>
                                                @endif
                                            </div>
                                            <p class="mb-0 text-white-50">{{ trans('core::video.camera_off') }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Media Controls --}}
                                <div class="media-controls position-absolute bottom-0 start-50 translate-middle-x mb-4">
                                    <div class="btn-group shadow-lg" role="group">
                                        <button type="button"
                                                wire:click="toggleMic"
                                                class="btn btn-lg {{ $micEnabled ? 'btn-light' : 'btn-danger' }} rounded-start-pill px-4">
                                            <i class="ti tabler-microphone{{ $micEnabled ? '' : '-off' }}"></i>
                                        </button>
                                        <button type="button"
                                                wire:click="toggleCamera"
                                                class="btn btn-lg {{ $cameraEnabled ? 'btn-light' : 'btn-danger' }} rounded-end-pill px-4">
                                            <i class="ti tabler-video{{ $cameraEnabled ? '' : '-off' }}"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Side - Meeting Info --}}
                        <div class="col-lg-5">
                            <div class="meeting-info p-4 h-100 d-flex flex-column">
                                {{-- Header --}}
                                <div class="text-center mb-4">
                                    <div class="mb-3">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                            <i class="ti tabler-video me-1"></i>
                                            {{ trans('core::video.video_consultation') }}
                                        </span>
                                    </div>
                                    <h4 class="mb-1">{{ trans('core::video.ready_to_join') }}</h4>
                                    <p class="text-muted small mb-0">{{ trans('core::video.check_settings') }}</p>
                                </div>

                                {{-- Appointment Details --}}
                                @if($bookingId)
                                    <div class="appointment-details bg-light rounded-3 p-3 mb-4">
                                        <h6 class="text-muted mb-3">
                                            <i class="ti tabler-calendar-event me-1"></i>
                                            {{ trans('core::video.appointment_details') }}
                                        </h6>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0">
                                                <span class="avatar avatar-sm bg-primary-subtle rounded-circle">
                                                    <i class="ti tabler-stethoscope text-primary"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <small class="text-muted d-block">{{ trans('core::video.doctor') }}</small>
                                                <span class="fw-medium">{{ $doctorName ?? '-' }}</span>
                                            </div>
                                        </div>
                                        @if($patientName)
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="flex-shrink-0">
                                                    <span class="avatar avatar-sm bg-info-subtle rounded-circle">
                                                        <i class="ti tabler-user text-info"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <small class="text-muted d-block">{{ trans('core::video.patient') }}</small>
                                                    <span class="fw-medium">{{ $patientName }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if($appointmentTime)
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <span class="avatar avatar-sm bg-success-subtle rounded-circle">
                                                        <i class="ti tabler-clock text-success"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <small class="text-muted d-block">{{ trans('core::video.time') }}</small>
                                                    <span class="fw-medium">{{ $appointmentTime }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Participant Info --}}
                                <div class="participant-info mb-4">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <div class="flex-shrink-0">
                                            @if($participantAvatar)
                                                <img src="{{ $participantAvatar }}" alt="{{ $participantName }}" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                            @else
                                                <span class="avatar avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($participantName, 0, 2)) }}</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $participantName }}</h6>
                                            <small class="text-muted">
                                                @if($participantRole === 'moderator')
                                                    <i class="ti tabler-shield-check text-primary me-1"></i>
                                                    {{ trans('core::video.role_moderator') }}
                                                @else
                                                    {{ trans('core::video.role_participant') }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Join Button --}}
                                <div class="mt-auto">
                                    <button wire:click="joinMeeting"
                                            class="btn btn-primary btn-lg w-100 py-3 rounded-pill shadow-sm">
                                        <i class="ti tabler-video me-2"></i>
                                        {{ trans('core::video.join_now') }}
                                    </button>

                                    {{-- Medical Examination Button --}}
                                    @if($participantRole === 'moderator' && $patientId)
                                        {{-- Doctor side: open existing exam or start a new one --}}
                                        @if($medicalExaminationId)
                                            <a href="{{ route('doctor.medicalExamination.create', $medicalExaminationId) }}"
                                               target="_blank"
                                               class="btn btn-outline-success btn-lg w-100 mt-2 py-3 rounded-pill">
                                                <i class="ti tabler-file-text me-2"></i>
                                                {{ trans('core::video.view_medical_examination') }}
                                            </a>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('doctor.medicalExamination.store', $patientId) }}"
                                                  target="_blank"
                                                  class="mt-2 mb-0">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-outline-success btn-lg w-100 py-3 rounded-pill">
                                                    <i class="ti tabler-file-text me-2"></i>
                                                    {{ trans('core::video.view_medical_examination') }}
                                                </button>
                                            </form>
                                        @endif
                                    @elseif($participantRole !== 'moderator' && $medicalExaminationId)
                                        {{-- Patient side: view their appointment / examination --}}
                                        <a href="{{ route('patient.appointments.show', $medicalExaminationId) }}"
                                           target="_blank"
                                           class="btn btn-outline-success btn-lg w-100 mt-2 py-3 rounded-pill">
                                            <i class="ti tabler-file-text me-2"></i>
                                            {{ trans('core::video.view_medical_examination') }}
                                        </a>
                                    @endif

                                    <div class="text-center mt-3">
                                        <button wire:click="copyMeetingLink" class="btn btn-link text-muted btn-sm">
                                            <i class="ti tabler-copy me-1"></i>
                                            {{ trans('core::video.copy_link') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="tips mt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-start">
                            <span class="badge bg-success-subtle text-success rounded-pill p-2 me-2">
                                <i class="ti tabler-wifi"></i>
                            </span>
                            <div>
                                <small class="fw-medium d-block">{{ trans('core::video.tip_connection') }}</small>
                                <small class="text-muted">{{ trans('core::video.tip_connection_desc') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start">
                            <span class="badge bg-info-subtle text-info rounded-pill p-2 me-2">
                                <i class="ti tabler-headphones"></i>
                            </span>
                            <div>
                                <small class="fw-medium d-block">{{ trans('core::video.tip_audio') }}</small>
                                <small class="text-muted">{{ trans('core::video.tip_audio_desc') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start">
                            <span class="badge bg-warning-subtle text-warning rounded-pill p-2 me-2">
                                <i class="ti tabler-bulb"></i>
                            </span>
                            <div>
                                <small class="fw-medium d-block">{{ trans('core::video.tip_lighting') }}</small>
                                <small class="text-muted">{{ trans('core::video.tip_lighting_desc') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Active Meeting --}}
    @if($isJoined && !$hasError)
        <div class="active-meeting">
            <div class="card border-0 shadow-lg overflow-hidden">
                {{-- Meeting Header --}}
                <div class="card-header bg-dark text-white py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-danger me-2 pulse-animation">
                                <i class="ti tabler-point-filled me-1"></i>
                                {{ trans('core::video.live') }}
                            </span>
                            <span class="text-white-50 small">
                                @if($doctorName && $patientName)
                                    {{ $doctorName }} & {{ $patientName }}
                                @else
                                    {{ $roomName }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="meeting-timer" class="badge bg-dark-subtle text-white">00:00</span>

                            {{-- Medical Examination Button --}}
                            @if($participantRole === 'moderator' && $patientId)
                                {{-- Doctor side --}}
                                @if($medicalExaminationId)
                                    <a href="{{ route('doctor.medicalExamination.create', $medicalExaminationId) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-success rounded-pill"
                                       title="{{ trans('core::video.view_medical_examination') }}">
                                        <i class="ti tabler-file-text"></i>
                                    </a>
                                @else
                                    <form method="POST"
                                          action="{{ route('doctor.medicalExamination.store', $patientId) }}"
                                          target="_blank"
                                          class="m-0 d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-success rounded-pill"
                                                title="{{ trans('core::video.view_medical_examination') }}">
                                            <i class="ti tabler-file-text"></i>
                                        </button>
                                    </form>
                                @endif
                            @elseif($participantRole !== 'moderator' && $medicalExaminationId)
                                {{-- Patient side --}}
                                <a href="{{ route('patient.appointments.show', $medicalExaminationId) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-success rounded-pill"
                                   title="{{ trans('core::video.view_medical_examination') }}">
                                    <i class="ti tabler-file-text"></i>
                                </a>
                            @endif

                            <button wire:click="copyMeetingLink"
                                    class="btn btn-sm btn-outline-light rounded-pill"
                                    title="{{ trans('core::video.copy_link') }}">
                                <i class="ti tabler-share"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Jitsi Container --}}
                <div id="jitsi-container" wire:ignore style="height: 600px; background: #1a1a2e;"></div>

                {{-- Meeting Footer --}}
                <div class="card-footer bg-light py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <small class="text-muted">
                            <i class="ti tabler-shield-check text-success me-1"></i>
                            {{ trans('core::video.secure_connection') }}
                        </small>
                        <button wire:click="leaveMeeting" class="btn btn-sm btn-outline-danger rounded-pill">
                            <i class="ti tabler-phone-off me-1"></i>
                            {{ trans('core::video.leave_meeting') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('page-script')
<script src="https://{{ config('core.video.providers.jitsi.domain', 'meet.jit.si') }}/external_api.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        let jitsiApi = null;
        let meetingTimer = null;
        let meetingStartTime = null;

        // Listen for join meeting event
        Livewire.on('meeting-joined', (data) => {
            initJitsiMeeting(data[0]);
        });

        // Listen for leave meeting event
        Livewire.on('meeting-left', () => {
            if (jitsiApi) {
                jitsiApi.dispose();
                jitsiApi = null;
            }
            stopTimer();
        });

        // Copy to clipboard
        Livewire.on('copy-to-clipboard', (data) => {
            navigator.clipboard.writeText(data[0].text).then(() => {
                // Show toast notification
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
            });
        });

        function initJitsiMeeting(data) {
            const container = document.getElementById('jitsi-container');
            if (!container) return;

            const config = @json($this->meetingConfig);

            const options = {
                roomName: config.roomName,
                parentNode: container,
                width: '100%',
                height: '100%',
                jwt: config.embedConfig.jwt || undefined,
                userInfo: {
                    displayName: config.embedConfig.userInfo.displayName,
                    email: config.embedConfig.userInfo.email,
                    avatarURL: config.embedConfig.userInfo.avatarURL
                },
                configOverwrite: {
                    ...config.embedConfig.configOverwrite,
                    startWithAudioMuted: !@json($micEnabled),
                    startWithVideoMuted: !@json($cameraEnabled),
                    // CRITICAL: Force disable prejoin/lobby for public Jitsi
                    prejoinPageEnabled: false,
                    prejoinConfig: {
                        enabled: false
                    },
                    enableLobby: false,
                    enableInsecureRoomNameWarning: false,
                },
                interfaceConfigOverwrite: config.embedConfig.interfaceConfigOverwrite,
                onload: function() {
                    startTimer();
                }
            };

            jitsiApi = new JitsiMeetExternalAPI(config.domain, options);

            // Event listeners
            jitsiApi.addEventListener('readyToClose', () => {
                @this.dispatch('meeting-ended');
            });

            jitsiApi.addEventListener('videoConferenceLeft', () => {
                @this.dispatch('meeting-ended');
            });

            jitsiApi.addEventListener('errorOccurred', (error) => {
                console.error('Jitsi Error:', error);

                // Extract meaningful error message
                let errorMessage = 'Unknown error';
                if (error?.error) {
                    if (error.error.name) {
                        errorMessage = error.error.name;
                        // Add details if available
                        if (error.error.message) {
                            errorMessage += ': ' + error.error.message;
                        }
                    } else if (error.error.message) {
                        errorMessage = error.error.message;
                    }
                }

                @this.dispatch('meeting-error', { message: errorMessage });
            });
        }

        function startTimer() {
            meetingStartTime = new Date();
            meetingTimer = setInterval(updateTimer, 1000);
        }

        function stopTimer() {
            if (meetingTimer) {
                clearInterval(meetingTimer);
                meetingTimer = null;
            }
        }

        function updateTimer() {
            if (!meetingStartTime) return;

            const now = new Date();
            const diff = Math.floor((now - meetingStartTime) / 1000);
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            const timerEl = document.getElementById('meeting-timer');
            if (timerEl) {
                if (hours > 0) {
                    timerEl.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                } else {
                    timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }
        }
    });
</script>

<style>
    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .video-room-container .avatar {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .video-room-container .avatar-md {
        width: 48px;
        height: 48px;
    }

    .video-room-container .avatar-sm {
        width: 32px;
        height: 32px;
    }

    .video-preview-container {
        border-radius: 0.5rem 0 0 0.5rem;
    }

    @media (max-width: 991.98px) {
        .video-preview-container {
            border-radius: 0.5rem 0.5rem 0 0;
            min-height: 300px !important;
        }
    }

    #jitsi-container iframe {
        border: none !important;
    }

    .bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
    .bg-success-subtle { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
    .bg-info-subtle { background-color: rgba(var(--bs-info-rgb), 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(var(--bs-warning-rgb), 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(var(--bs-danger-rgb), 0.1) !important; }
    .bg-dark-subtle { background-color: rgba(0, 0, 0, 0.3) !important; }
</style>
@endpush
