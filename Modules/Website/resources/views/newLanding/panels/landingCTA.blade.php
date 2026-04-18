@php
    // Support both dynamic panel data and fallback to sections
    if (isset($panel)) {
        $ctaTitle = $panel['title'][$locale] ?? 'Ready to Get Started?';
        $ctaSubtitle = $panel['settings']['description'][$locale] ?? 'Start your project with a 14-day free trial';
        $ctaButtonText = $panel['settings']['button_text'][$locale] ?? 'Book Appointment';
        $ctaButtonUrl = $panel['settings']['button_url'] ?? (route('booking.public') ?? '#booking');
    } else {
        $cta = $sections['cta'] ?? [];
        $ctaTitle = $cta['title'][$locale] ?? 'Ready to Get Started?';
        $ctaSubtitle = $cta['subtitle'][$locale] ?? 'Start your project with a 14-day free trial';
        $ctaButtonText = $cta['button_text'][$locale] ?? 'Book Appointment';
        $ctaButtonUrl = $cta['button_url'] ?? (route('booking.public') ?? '#booking');
    }
@endphp

<!-- CTA: Start -->
<section id="landingCTA" class="section-py landing-cta position-relative p-lg-0 pb-0">
    <img
        src="{{ asset('assets/img/front-pages/backgrounds/cta-bg-' . $configData['theme'] . '.png') }}"
        class="position-absolute bottom-0 end-0 scaleX-n1-rtl h-100 w-100 z-n1"
        alt="cta image"
        data-app-light-img="front-pages/backgrounds/cta-bg-light.png"
        data-app-dark-img="front-pages/backgrounds/cta-bg-dark.png" />
    <div class="container">
        <div class="row align-items-center gy-5 gy-lg-0">
            <div class="col-lg-6 text-center text-lg-start">
                <h6 class="h2 text-primary fw-bold mb-1">{{ $ctaTitle }}</h6>
                <p class="fw-medium mb-4">{{ $ctaSubtitle }}</p>
                <a href="{{ $ctaButtonUrl }}" class="btn btn-lg btn-primary">{{ $ctaButtonText }}</a>
            </div>
            <div class="col-lg-6 pt-lg-5 text-center text-lg-end">
                <img
                    src="{{ asset('assets/img/front-pages/landing-page/cta-dashboard.png') }}"
                    alt="cta dashboard"
                    class="img-fluid" />
            </div>
        </div>
    </div>
</section>
<!-- CTA: End -->
