@php
    use Illuminate\Support\Facades\Route;
    $currentRouteName = Route::currentRouteName();
    $activeRoutes = ['front-pages-pricing', 'front-pages-payment', 'front-pages-checkout', 'front-pages-help-center'];
    $activeClass = in_array($currentRouteName, $activeRoutes) ? 'active' : '';
@endphp

@section('vendor-script')
    @vite(['resources/assets/vendor/js/dropdown-hover.js', 'resources/assets/vendor/js/mega-dropdown.js'], 'build/modules/theme')
@endsection
<!-- Navbar: Start -->
<nav class="layout-navbar shadow-none py-0">
    <div class="container">
        <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
            <!-- Menu logo wrapper: Start -->
            <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8 ms-0">
                <!-- Mobile menu toggle: Start-->
                <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false"
                        aria-label="Toggle navigation">
                    <i class="icon-base ti tabler-menu-2 icon-lg align-middle text-heading fw-medium"></i>
                </button>
                <!-- Mobile menu toggle: End-->
                <a href="{{route('landing.home')}}" class="app-brand-link">
                    <span class="app-brand-logo demo">@includeIf('theme::user/_partials.macros')</span>
                    <span class="app-brand-text demo menu-text fw-bold ms-2 ps-1">{{ config('variables.templateName') }}</span>
                </a>
            </div>
            <!-- Menu logo wrapper: End -->
            <!-- Menu wrapper: Start -->
            <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
                <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2"
                        type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="icon-base ti tabler-x icon-lg"></i>
                </button>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" aria-current="page"
                           href="#landingHero">
                            {{trans('newLandingPage.navbar.home')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#landingFeatures">
                            {{trans('newLandingPage.navbar.features')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#booking">
                            Book Appointment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#theHow">
                            {{trans('newLandingPage.navbar.theHow')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#contactUs">
                            {{trans('newLandingPage.navbar.contactUs')}}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="landing-menu-overlay d-lg-none"></div>
            <!-- Menu wrapper: End -->
            <!-- Toolbar: Start -->
            <ul class="navbar-nav flex-row align-items-center ms-auto">
                @includeIf('theme::user.layouts.sections.navbar.navbar-languages')

                <!-- Style Switcher (Theme Mode) -->
                @includeIf('theme::user.layouts.sections.navbar.navbar-theme-switcher')
                <!-- / Style Switcher-->

                <!-- navbar button: Start -->
                <li>
                    <a href="{{ route('booking.public') }}" class="btn btn-primary me-2"><span
                                class="icon-base ti tabler-calendar scaleX-n1-rtl me-md-1"></span><span
                                class="d-none d-md-block">Book Appointment</span></a>
                </li>
                {{--
                    Login / Dashboard CTA.
                    Behaviour:
                      - Doctor already logged in  → "Dashboard" button → doctor.dashboard
                      - Patient already logged in → "Dashboard" button → patient.dashboard
                      - Guest                     → "Login / Register" button → patient.login
                    The doctor guard is checked first so a doctor viewing the
                    public site is always sent to their own dashboard even if
                    they also have a patient session cookie.
                --}}
                <li>
                    @auth('doctor')
                        {{-- Logged-in doctor → doctor dashboard. --}}
                        <a href="{{ route('doctor.dashboard') }}" class="btn btn-outline-primary">
                            <span class="icon-base ti tabler-layout-dashboard scaleX-n1-rtl me-md-1"></span>
                            <span class="d-none d-md-block">{{ trans('customer.sidebar.dashboard') ?? 'Dashboard' }}</span>
                        </a>
                    @elseauth('web')
                        {{-- Logged-in patient (default web guard) → patient dashboard. --}}
                        <a href="{{ route('patient.dashboard') }}" class="btn btn-outline-primary">
                            <span class="icon-base ti tabler-layout-dashboard scaleX-n1-rtl me-md-1"></span>
                            <span class="d-none d-md-block">{{ trans('customer.sidebar.dashboard') ?? 'Dashboard' }}</span>
                        </a>
                    @else
                        {{-- Guest → patient login page (default public login entry point). --}}
                        <a href="{{ route('patient.login') }}" class="btn btn-outline-primary">
                            <span class="icon-base ti tabler-login scaleX-n1-rtl me-md-1"></span>
                            <span class="d-none d-md-block">{{ trans('newLandingPage.navbar.loginRegister') }}</span>
                        </a>
                    @endauth
                </li>
                <!-- navbar button: End -->
            </ul>
            <!-- Toolbar: End -->
        </div>
    </div>
</nav>
<!-- Navbar: End -->
