<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trans('core::video.video_consultation') }} - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">

    @livewireStyles

    <style>
        :root {
            --bs-primary-rgb: 105, 108, 255;
            --bs-success-rgb: 40, 199, 111;
            --bs-info-rgb: 0, 207, 232;
            --bs-warning-rgb: 255, 159, 67;
            --bs-danger-rgb: 234, 84, 85;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
        }

        .video-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .video-room-container {
            max-width: 1200px;
            width: 100%;
        }

        .bg-primary-subtle { background-color: rgba(var(--bs-primary-rgb), 0.1) !important; }
        .bg-success-subtle { background-color: rgba(var(--bs-success-rgb), 0.1) !important; }
        .bg-info-subtle { background-color: rgba(var(--bs-info-rgb), 0.1) !important; }
        .bg-warning-subtle { background-color: rgba(var(--bs-warning-rgb), 0.1) !important; }
        .bg-danger-subtle { background-color: rgba(var(--bs-danger-rgb), 0.1) !important; }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="video-wrapper">
        <livewire:core::video-room
            :room-name="$roomName"
            :participant-id="(string) $participant->id"
            :participant-name="$participant->name"
            :participant-email="$participant->email"
            :participant-role="$participant->role->value"
            :participant-avatar="$participant->avatar"
            :room-url="$config['roomUrl']"
            :booking-id="$bookingId ?? null"
            :doctor-name="$doctorName ?? null"
            :patient-name="$patientName ?? null"
            :appointment-time="$appointmentTime ?? null"
            :on-end-redirect-url="$returnUrl ?? '/'"
        />
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @livewireScripts
    @stack('page-script')
</body>
</html>
