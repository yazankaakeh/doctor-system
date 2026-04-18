@extends('theme::user.layouts.layoutFront')

@section('title', $doctor->name . ' - ' . trans('website::doctors.doctor_profile'))

@section('meta')
    <meta name="description" content="{{ $doctor->bio ?? trans('website::doctors.doctor_profile_meta', ['name' => $doctor->name]) }}">
    @if($doctor->getFirstMediaUrl('img'))
        <meta property="og:image" content="{{ $doctor->getFirstMediaUrl('img') }}">
    @endif
@endsection

@section('vendor-style')
    <style>
        :root {
            --theme-primary: var(--bs-primary, #1EAAE7);
            --theme-primary-rgb: var(--bs-primary-rgb, 30, 170, 231);
            --theme-secondary: #092C4C;
            --theme-success: var(--bs-success, #28C76F);
            --theme-success-rgb: var(--bs-success-rgb, 40, 199, 111);
            --theme-info: var(--bs-info, #17a2b8);
            --theme-warning: var(--bs-warning, #FF9900);

            /* Light mode */
            --card-bg: var(--bs-body-bg, #fff);
            --card-border: var(--bs-border-color, rgba(0, 0, 0, 0.08));
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            --card-shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.12);
            --text-heading: var(--bs-body-color, #1a1a2e);
            --text-body: var(--bs-body-color, #4a5568);
            --text-muted: var(--bs-secondary-color, #718096);
            --surface-light: #f8fafc;
            --avatar-border: #fff;
            --gradient-start: var(--theme-secondary);
            --gradient-end: var(--theme-primary);
        }

        [data-bs-theme="dark"] {
            --card-bg: var(--bs-body-bg, #1e1e2d);
            --card-border: rgba(255, 255, 255, 0.1);
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
            --card-shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.4);
            --text-heading: #f1f1f1;
            --text-body: #a0aec0;
            --text-muted: #718096;
            --surface-light: rgba(255, 255, 255, 0.03);
            --avatar-border: #2d2d3f;
        }

        /* Hero Section */
        .doctor-hero {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            padding: 5rem 0 12rem;
            position: relative;
            overflow: hidden;
        }

        .doctor-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
        }

        .doctor-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(to top, var(--bs-body-bg, #fff) 0%, transparent 100%);
        }

        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            display: inline-flex;
        }

        .breadcrumb-custom .breadcrumb {
            margin: 0;
        }

        .breadcrumb-custom .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb-custom .breadcrumb-item a:hover {
            color: #fff;
        }

        .breadcrumb-custom .breadcrumb-item.active {
            color: #fff;
            font-weight: 600;
        }

        .breadcrumb-custom .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Profile Card */
        .profile-wrapper {
            margin-top: -10rem;
            position: relative;
            z-index: 10;
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--card-border);
            overflow: hidden;
        }

        .profile-header {
            position: relative;
            padding: 2.5rem 2rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        @media (min-width: 768px) {
            .profile-header {
                flex-direction: row;
                align-items: flex-start;
                padding: 2.5rem;
            }
        }

        .avatar-container {
            position: relative;
            flex-shrink: 0;
        }

        .avatar-ring {
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--theme-primary), var(--theme-success));
            animation: pulse-ring 2s ease-out infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.1); opacity: 0; }
        }

        .avatar-img {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--avatar-border);
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .avatar-placeholder {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid var(--avatar-border);
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .avatar-placeholder span {
            font-size: 3.5rem;
            font-weight: 700;
            color: #fff;
        }

        .verified-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 36px;
            height: 36px;
            background: var(--theme-success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            z-index: 2;
            border: 3px solid var(--avatar-border);
            box-shadow: 0 4px 12px rgba(var(--theme-success-rgb), 0.4);
        }

        .profile-info {
            flex: 1;
            text-align: center;
            margin-top: 1.5rem;
        }

        @media (min-width: 768px) {
            .profile-info {
                text-align: start;
                margin-top: 0;
                margin-left: 2rem;
            }
        }

        .specialty-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(var(--theme-primary-rgb), 0.1);
            color: var(--theme-primary);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        [data-bs-theme="dark"] .specialty-badge {
            background: rgba(var(--theme-primary-rgb), 0.2);
        }

        .doctor-name {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .doctor-bio-short {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
            max-width: 500px;
        }

        .profile-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .profile-actions {
                justify-content: flex-start;
            }
        }

        .btn-book {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, var(--theme-success), #1fa55a);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(var(--theme-success-rgb), 0.3);
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(var(--theme-success-rgb), 0.4);
            color: #fff;
        }

        .btn-contact {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            background: var(--card-bg);
            color: var(--text-heading);
            border: 2px solid var(--card-border);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-contact:hover {
            border-color: var(--theme-primary);
            color: var(--theme-primary);
            background: rgba(var(--theme-primary-rgb), 0.05);
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1px;
            background: var(--card-border);
            border-top: 1px solid var(--card-border);
        }

        @media (min-width: 768px) {
            .stats-section {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .stat-item {
            background: var(--card-bg);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            background: var(--surface-light);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.25rem;
        }

        /* Content Section */
        .content-section {
            padding: 3rem 0;
        }

        .section-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .section-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .section-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-heading);
        }

        .section-body {
            padding: 1.5rem;
        }

        .bio-text {
            font-size: 1rem;
            line-height: 1.8;
            color: var(--text-body);
        }

        /* Clinic Cards */
        .clinic-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--surface-light);
            border-radius: 12px;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .clinic-item:last-child {
            margin-bottom: 0;
        }

        .clinic-item:hover {
            border-color: var(--theme-primary);
            transform: translateX(4px);
        }

        .clinic-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .clinic-info {
            margin-left: 1rem;
        }

        .clinic-name {
            font-weight: 600;
            color: var(--text-heading);
            margin-bottom: 0.25rem;
        }

        .clinic-address {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Availability Slots */
        .slots-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .slot-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            background: rgba(var(--theme-success-rgb), 0.1);
            border: 1px solid rgba(var(--theme-success-rgb), 0.2);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--theme-success);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .slot-item:hover {
            background: var(--theme-success);
            border-color: var(--theme-success);
            color: #fff;
            transform: translateY(-2px);
        }

        [data-bs-theme="dark"] .slot-item {
            background: rgba(var(--theme-success-rgb), 0.15);
        }

        /* Contact Card */
        .contact-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--card-border);
        }

        .contact-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .contact-item:first-child {
            padding-top: 0;
        }

        .contact-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .contact-details {
            margin-left: 1rem;
        }

        .contact-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contact-value {
            font-weight: 600;
            color: var(--text-heading);
        }

        /* CTA Card */
        .cta-card {
            background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .cta-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .cta-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .cta-text {
            opacity: 0.9;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 1rem 1.5rem;
            background: #fff;
            color: var(--theme-secondary);
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .cta-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: var(--theme-secondary);
        }

        /* Share Buttons */
        .share-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .share-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            border-radius: 12px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .share-btn:hover {
            transform: translateY(-2px);
            color: #fff;
        }

        .share-btn.facebook { background: #1877f2; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.whatsapp { background: #25d366; }
        .share-btn.copy {
            background: var(--surface-light);
            color: var(--text-heading);
            border: 1px solid var(--card-border);
        }

        .share-btn.copy:hover {
            background: var(--theme-primary);
            color: #fff;
            border-color: var(--theme-primary);
        }

        /* Related Doctors Section */
        .related-section {
            padding: 4rem 0;
            background: var(--surface-light);
        }

        [data-bs-theme="dark"] .related-section {
            background: rgba(0, 0, 0, 0.2);
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: rgba(var(--theme-primary-rgb), 0.1);
            color: var(--theme-primary);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .section-title h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 0.5rem;
        }

        .section-title p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .doctor-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
        }

        .doctor-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
            border-color: transparent;
        }

        .doctor-card-img {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .doctor-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .doctor-card:hover .doctor-card-img img {
            transform: scale(1.1);
        }

        .doctor-card-img-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doctor-card-img-placeholder i {
            font-size: 4rem;
            color: rgba(255, 255, 255, 0.3);
        }

        .doctor-card-body {
            padding: 1.25rem;
            text-align: center;
        }

        .doctor-card-name {
            font-weight: 700;
            color: var(--text-heading);
            margin-bottom: 0.25rem;
        }

        .doctor-card-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Sticky Sidebar */
        @media (min-width: 992px) {
            .sidebar-sticky {
                position: sticky;
                top: 100px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.5s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
    </style>
@endsection

@section('content')
    {{-- Hero Section --}}
    <section class="doctor-hero first-section-pt">
        <div class="container position-relative" style="z-index: 1;">
            <div class="text-center">
                <div class="breadcrumb-custom">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('landing.home') }}">
                                <i class="ti tabler-home me-1"></i>{{ trans('website::doctors.home') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('doctors.index') }}">{{ trans('website::doctors.our_doctors') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $doctor->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- Profile Card --}}
    <section class="profile-wrapper">
        <div class="container">
            <div class="profile-card animate-in">
                <div class="profile-header">
                    {{-- Avatar --}}
                    <div class="avatar-container">
                        <div class="avatar-ring"></div>
                        @if($doctor->getFirstMediaUrl('img'))
                            <img src="{{ $doctor->getFirstMediaUrl('img', 'preview') }}"
                                 alt="{{ $doctor->name }}"
                                 class="avatar-img">
                        @else
                            <div class="avatar-placeholder">
                                <span>{{ strtoupper(substr($doctor->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="verified-badge">
                            <i class="ti tabler-check"></i>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="profile-info">
                        @if($doctor->medicalSpecialty)
                            <span class="specialty-badge">
                                <i class="ti tabler-stethoscope"></i>
                                {{ $doctor->medicalSpecialty->name }}
                            </span>
                        @endif

                        <h1 class="doctor-name">{{ $doctor->name }}</h1>

                        @if($doctor->bio)
                            <p class="doctor-bio-short">{{ Str::limit($doctor->bio, 120) }}</p>
                        @endif

                        <div class="profile-actions">
                            <a href="{{ route('booking.public') }}" class="btn-book">
                                <i class="ti tabler-calendar-plus"></i>
                                {{ trans('website::doctors.book_appointment') }}
                            </a>
                            @if($doctor->phone)
                                <a href="tel:{{ $doctor->phone }}" class="btn-contact">
                                    <i class="ti tabler-phone"></i>
                                    {{ trans('website::doctors.contact_info') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="stats-section">
                    <div class="stat-item">
                        <div class="stat-icon bg-label-primary text-primary">
                            <i class="ti tabler-stethoscope"></i>
                        </div>
                        <div class="stat-value">{{ number_format($stats['total_examinations']) }}</div>
                        <div class="stat-label">{{ trans('website::doctors.medical_examinations') }}</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon bg-label-success text-success">
                            <i class="ti tabler-users"></i>
                        </div>
                        <div class="stat-value">{{ number_format($stats['total_patients']) }}</div>
                        <div class="stat-label">{{ trans('website::doctors.patients_treated') }}</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon bg-label-info text-info">
                            <i class="ti tabler-calendar-check"></i>
                        </div>
                        <div class="stat-value">{{ number_format($stats['completed_bookings']) }}</div>
                        <div class="stat-label">{{ trans('website::doctors.completed_appointments') }}</div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon bg-label-warning text-warning">
                            <i class="ti tabler-building-hospital"></i>
                        </div>
                        <div class="stat-value">{{ $doctor->clinics_count }}</div>
                        <div class="stat-label">{{ trans('website::doctors.clinics') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="content-section bg-body">
        <div class="container">
            <div class="row g-4">
                {{-- Main Content --}}
                <div class="col-lg-8">
                    {{-- About --}}
                    @if($doctor->bio)
                        <div class="section-card animate-in delay-1">
                            <div class="section-header">
                                <div class="section-header-icon bg-label-primary text-primary">
                                    <i class="ti tabler-user-circle"></i>
                                </div>
                                <h5>{{ trans('website::doctors.about_doctor') }}</h5>
                            </div>
                            <div class="section-body">
                                <p class="bio-text mb-0">{{ $doctor->bio }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Clinics --}}
                    @if($doctor->clinics && $doctor->clinics->count() > 0)
                        <div class="section-card animate-in delay-2">
                            <div class="section-header">
                                <div class="section-header-icon bg-label-warning text-warning">
                                    <i class="ti tabler-building-hospital"></i>
                                </div>
                                <h5>{{ trans('website::doctors.clinics') }}</h5>
                            </div>
                            <div class="section-body">
                                @foreach($doctor->clinics as $clinic)
                                    <div class="clinic-item">
                                        <div class="clinic-icon bg-label-primary text-primary">
                                            <i class="ti tabler-building"></i>
                                        </div>
                                        <div class="clinic-info">
                                            <div class="clinic-name">{{ $clinic->name }}</div>
                                            @if($clinic->address)
                                                <div class="clinic-address">
                                                    <i class="ti tabler-map-pin me-1"></i>{{ $clinic->address }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Available Slots --}}
                    @if($doctor->availabilities && $doctor->availabilities->count() > 0)
                        <div class="section-card animate-in delay-3">
                            <div class="section-header">
                                <div class="section-header-icon bg-label-success text-success">
                                    <i class="ti tabler-calendar"></i>
                                </div>
                                <h5>{{ trans('website::doctors.available_slots') }}</h5>
                            </div>
                            <div class="section-body">
                                <p class="text-muted mb-3">{{ trans('website::doctors.upcoming_availability') }}</p>
                                <div class="slots-grid">
                                    @foreach($doctor->availabilities as $availability)
                                        <a href="{{ route('booking.public') }}" class="slot-item">
                                            <i class="ti tabler-clock"></i>
                                            {{ $availability->date->format('M d') }} -
                                            {{ \Carbon\Carbon::parse($availability->start_time)->format('h:i A') }}
                                        </a>
                                    @endforeach
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('booking.public') }}" class="btn btn-primary">
                                        <i class="ti tabler-calendar-plus me-2"></i>{{ trans('website::doctors.view_all_slots') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="col-lg-4">
                    <div class="sidebar-sticky">
                        {{-- CTA Card --}}
                        <div class="cta-card animate-in delay-1">
                            <div class="cta-icon">
                                <i class="ti tabler-calendar-heart"></i>
                            </div>
                            <h4 class="cta-title">{{ trans('website::doctors.ready_to_book') }}</h4>
                            <p class="cta-text">{{ trans('website::doctors.book_appointment_desc') }}</p>
                            <a href="{{ route('booking.public') }}" class="cta-btn">
                                <i class="ti tabler-calendar-plus"></i>
                                {{ trans('website::doctors.book_now') }}
                            </a>
                        </div>

                        {{-- Contact Info --}}
                        <div class="section-card animate-in delay-2">
                            <div class="section-header">
                                <div class="section-header-icon bg-label-info text-info">
                                    <i class="ti tabler-info-circle"></i>
                                </div>
                                <h5>{{ trans('website::doctors.contact_info') }}</h5>
                            </div>
                            <div class="section-body">
                                @if($doctor->email)
                                    <div class="contact-item">
                                        <div class="contact-icon bg-label-primary text-primary">
                                            <i class="ti tabler-mail"></i>
                                        </div>
                                        <div class="contact-details">
                                            <div class="contact-label">{{ trans('website::doctors.email') }}</div>
                                            <div class="contact-value">{{ $doctor->email }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if($doctor->phone)
                                    <div class="contact-item">
                                        <div class="contact-icon bg-label-success text-success">
                                            <i class="ti tabler-phone"></i>
                                        </div>
                                        <div class="contact-details">
                                            <div class="contact-label">{{ trans('website::doctors.phone') }}</div>
                                            <div class="contact-value">{{ $doctor->phone }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if($doctor->medicalSpecialty)
                                    <div class="contact-item">
                                        <div class="contact-icon bg-label-info text-info">
                                            <i class="ti tabler-stethoscope"></i>
                                        </div>
                                        <div class="contact-details">
                                            <div class="contact-label">{{ trans('website::doctors.specialty') }}</div>
                                            <div class="contact-value">{{ $doctor->medicalSpecialty->name }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if($doctor->gender)
                                    <div class="contact-item">
                                        <div class="contact-icon bg-label-warning text-warning">
                                            <i class="ti tabler-gender-bigender"></i>
                                        </div>
                                        <div class="contact-details">
                                            <div class="contact-label">{{ trans('website::doctors.gender') }}</div>
                                            <div class="contact-value">{{ $doctor->gender->label() }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Share --}}
                        <div class="section-card animate-in delay-3">
                            <div class="section-header">
                                <div class="section-header-icon bg-label-secondary text-secondary">
                                    <i class="ti tabler-share"></i>
                                </div>
                                <h5>{{ trans('website::doctors.share_profile') }}</h5>
                            </div>
                            <div class="section-body">
                                <div class="share-buttons">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                       target="_blank" class="share-btn facebook">
                                        <i class="ti tabler-brand-facebook"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($doctor->name) }}"
                                       target="_blank" class="share-btn twitter">
                                        <i class="ti tabler-brand-twitter"></i>
                                    </a>
                                    <a href="https://wa.me/?text={{ urlencode($doctor->name . ' - ' . url()->current()) }}"
                                       target="_blank" class="share-btn whatsapp">
                                        <i class="ti tabler-brand-whatsapp"></i>
                                    </a>
                                    <button type="button" class="share-btn copy" onclick="copyToClipboard('{{ url()->current() }}')">
                                        <i class="ti tabler-link"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Related Doctors --}}
    @if($relatedDoctors->count() > 0)
        <section class="related-section">
            <div class="container">
                <div class="section-title">
                    <span class="section-badge">{{ trans('website::doctors.more_doctors') }}</span>
                    <h2>{{ trans('website::doctors.related_doctors') }}</h2>
                    <p>{{ trans('website::doctors.same_specialty_doctors') }}</p>
                </div>

                <div class="row g-4">
                    @foreach($relatedDoctors as $relatedDoctor)
                        <div class="col-sm-6 col-lg-3">
                            <a href="{{ route('doctors.show', $relatedDoctor) }}" class="text-decoration-none">
                                <div class="doctor-card">
                                    <div class="doctor-card-img">
                                        @if($relatedDoctor->getFirstMediaUrl('img'))
                                            <img src="{{ $relatedDoctor->getFirstMediaUrl('img', 'preview') }}"
                                                 alt="{{ $relatedDoctor->name }}">
                                        @else
                                            <div class="doctor-card-img-placeholder">
                                                <i class="ti tabler-user"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="doctor-card-body">
                                        <h6 class="doctor-card-name">{{ $relatedDoctor->name }}</h6>
                                        <div class="doctor-card-meta">
                                            {{ $relatedDoctor->medical_examinations_count }} {{ trans('website::doctors.examinations') }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-5">
                    <a href="{{ route('doctors.index') }}" class="btn btn-primary btn-lg px-4">
                        <i class="ti tabler-users me-2"></i>{{ trans('website::doctors.view_all_doctors') }}
                    </a>
                </div>
            </div>
        </section>
    @endif
@endsection

@section('page-script')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const btn = document.querySelector('.share-btn.copy');
                const originalIcon = btn.innerHTML;
                btn.innerHTML = '<i class="ti tabler-check"></i>';
                btn.style.background = 'var(--theme-success)';
                btn.style.color = '#fff';

                setTimeout(() => {
                    btn.innerHTML = originalIcon;
                    btn.style.background = '';
                    btn.style.color = '';
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
@endsection
