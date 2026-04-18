@extends('theme::user.layouts.layoutFront')

@section('title', trans('website::doctors.our_doctors'))

@section('vendor-style')
    <style>
        :root {
            --theme-primary: var(--bs-primary, #1EAAE7);
            --theme-primary-rgb: var(--bs-primary-rgb, 30, 170, 231);
            --theme-primary-dark: #1890c7;
            --theme-secondary: #092C4C;
            --theme-success: var(--bs-success, #28C76F);
            --theme-success-rgb: var(--bs-success-rgb, 40, 199, 111);

            /* Light mode defaults */
            --card-bg: var(--bs-body-bg, #fff);
            --card-border: var(--bs-border-color, rgba(9, 44, 76, 0.08));
            --card-shadow: 0 4px 20px rgba(9, 44, 76, 0.08);
            --card-shadow-hover: 0 20px 40px rgba(var(--theme-primary-rgb), 0.15);
            --text-primary: var(--bs-body-color, #092C4C);
            --text-muted: var(--bs-secondary-color, #6c757d);
        }

        /* Dark mode overrides */
        [data-bs-theme="dark"] {
            --card-bg: var(--bs-body-bg, #1a1d21);
            --card-border: var(--bs-border-color, rgba(255, 255, 255, 0.1));
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            --card-shadow-hover: 0 20px 40px rgba(var(--theme-primary-rgb), 0.25);
            --text-primary: var(--bs-body-color, #e4e6eb);
            --text-muted: var(--bs-secondary-color, #a0a4a8);
        }

        .doctors-hero {
            background: linear-gradient(135deg, var(--theme-secondary) 0%, #0d3a5c 50%, var(--theme-primary) 100%);
            padding: 6rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .doctors-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231EAAE7' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .doctor-card {
            border: 1px solid var(--card-border);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
        }

        .doctor-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-shadow-hover);
            border-color: transparent;
        }

        .doctor-card-img {
            position: relative;
            height: 280px;
            overflow: hidden;
        }

        .doctor-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .doctor-card:hover .doctor-card-img img {
            transform: scale(1.08);
        }

        .doctor-card-img-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doctor-card-img-placeholder i {
            font-size: 5rem;
            color: rgba(255, 255, 255, 0.3);
        }

        .doctor-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 2rem 1.5rem 1rem;
            background: linear-gradient(to top, rgba(9, 44, 76, 0.95), transparent);
        }

        .specialty-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 1rem;
            background: rgba(var(--theme-primary-rgb), 0.9);
            color: #fff;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .doctor-card-body {
            padding: 1.5rem;
        }

        .doctor-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .doctor-stats {
            display: flex;
            gap: 1.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--card-border);
        }

        .doctor-stat {
            text-align: center;
            flex: 1;
        }

        .doctor-stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--theme-primary);
        }

        .doctor-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .filter-card .form-label {
            color: var(--text-primary);
        }

        .filter-card .form-control,
        .filter-card .form-select {
            background-color: var(--bs-body-bg);
            border-color: var(--card-border);
            color: var(--text-primary);
        }

        .filter-card .form-control:focus,
        .filter-card .form-select:focus {
            border-color: var(--theme-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--theme-primary-rgb), 0.25);
        }

        .filter-card .input-group-text {
            background-color: var(--bs-body-bg);
            border-color: var(--card-border);
            color: var(--text-muted);
        }

        .btn-book-now {
            background: linear-gradient(135deg, var(--theme-success) 0%, #1fa55a 100%);
            border: none;
            color: #fff;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-book-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--theme-success-rgb), 0.4);
            color: #fff;
        }

        .btn-view-profile {
            background: transparent;
            border: 2px solid var(--theme-primary);
            color: var(--theme-primary);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-view-profile:hover {
            background: var(--theme-primary);
            color: #fff;
        }

        /* Empty state dark mode */
        [data-bs-theme="dark"] .text-muted {
            color: var(--text-muted) !important;
        }
    </style>
@endsection

@section('content')
    {{-- Hero Section --}}
    <section class="doctors-hero first-section-pt">
        <div class="container position-relative" style="z-index: 1;">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold text-white mb-3">{{ trans('website::doctors.our_doctors') }}</h1>
                    <p class="lead text-white opacity-75 mb-0">{{ trans('website::doctors.doctors_subtitle') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content --}}
    <section class="section-py bg-body">
        <div class="container">
            {{-- Filters --}}
            <div class="filter-card">
                <form action="{{ route('doctors.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">{{ trans('website::doctors.search') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent">
                                    <i class="ti tabler-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="{{ trans('website::doctors.search_placeholder') }}"
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('website::doctors.specialty') }}</label>
                            <select name="specialty" class="form-select">
                                <option value="">{{ trans('website::doctors.all_specialties') }}</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" {{ request('specialty') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti tabler-filter me-2"></i>{{ trans('website::doctors.filter') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Doctors Grid --}}
            @if($doctors->count() > 0)
                <div class="row g-4">
                    @foreach($doctors as $doctor)
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="doctor-card h-100">
                                <div class="doctor-card-img">
                                    @if($doctor->getFirstMediaUrl('img'))
                                        <img src="{{ $doctor->getFirstMediaUrl('img', 'preview') }}"
                                             alt="{{ $doctor->name }}">
                                    @else
                                        <div class="doctor-card-img-placeholder">
                                            <i class="ti tabler-user"></i>
                                        </div>
                                    @endif
                                    <div class="doctor-card-overlay">
                                        @if($doctor->medicalSpecialty)
                                            <span class="specialty-badge">
                                                <i class="ti tabler-stethoscope me-1"></i>
                                                {{ $doctor->medicalSpecialty->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="doctor-card-body">
                                    <h5 class="doctor-name">{{ $doctor->name }}</h5>

                                    <div class="doctor-stats">
                                        <div class="doctor-stat">
                                            <div class="doctor-stat-value">{{ $doctor->medical_examinations_count }}</div>
                                            <div class="doctor-stat-label">{{ trans('website::doctors.examinations') }}</div>
                                        </div>
                                        <div class="doctor-stat">
                                            <div class="doctor-stat-value">{{ $doctor->bookings_count }}</div>
                                            <div class="doctor-stat-label">{{ trans('website::doctors.bookings') }}</div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 mt-3">
                                        <a href="{{ route('doctors.show', $doctor) }}" class="btn-view-profile">
                                            <i class="ti tabler-eye me-1"></i>{{ trans('website::doctors.view_profile') }}
                                        </a>
                                        <a href="{{ route('booking.public') }}" class="btn-book-now">
                                            <i class="ti tabler-calendar-plus me-1"></i>{{ trans('website::doctors.book_now') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-5">
                    {{ $doctors->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ti tabler-user-off display-1 text-muted mb-4"></i>
                    <h4 class="text-muted">{{ trans('website::doctors.no_doctors_found') }}</h4>
                    <p class="text-muted mb-4">{{ trans('website::doctors.try_different_search') }}</p>
                    <a href="{{ route('doctors.index') }}" class="btn btn-primary">
                        <i class="ti tabler-refresh me-2"></i>{{ trans('website::doctors.clear_filters') }}
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
