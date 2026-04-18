@php
    $title = $panel['title'][app()->getLocale()] ?? $panel['title']['en'] ?? '';
    $settings = $panel['settings'] ?? [];
    $textColor = $settings['text_color'] ?? '#333';
@endphp

<section id="booking" class="py-5">
    <div class="container">
        @if($title)
            <div class="text-center mb-5">
                <h2 class="fw-bold">{{ $title }}</h2>
                @if(!empty($settings['subtitle']))
                    <p class="text-muted lead">{{ $settings['subtitle'] }}</p>
                @endif
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-10">
                @livewire('booking::public-booking-wizard')
            </div>
        </div>
    </div>
</section>
