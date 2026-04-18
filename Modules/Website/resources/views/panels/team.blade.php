@php
    $locale = app()->getLocale();
    $isRtl = in_array($locale, ['ar', 'he', 'fa']);
    $title = $panel->getTranslation('title', $locale);
    $badge = $panel->settings['badge'][$locale] ?? null;
    $description = $panel->settings['description'][$locale] ?? null;
@endphp

@once
    @include('website::panels._panels-styles')
@endonce

<section class="panel-section panel-bg-white" id="panel-{{ $panel->id }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="container">
        {{-- Section Header --}}
        <div class="panel-header">
            @if($badge)
                <span class="panel-badge">{{ $badge }}</span>
            @endif
            @if($title)
                <h2 class="panel-title">{{ $title }}</h2>
            @endif
            @if($description)
                <p class="panel-description">{{ $description }}</p>
            @endif
        </div>

        @php
            $showViewAllDoctors = $panel->settings['show_view_all_doctors'] ?? false;
        @endphp

        {{-- Team Grid --}}
        @if($panel->activeItems->count() > 0)
            <div class="row g-4 justify-content-center">
                @foreach($panel->activeItems as $index => $item)
                    @php
                        $name = $item->getTranslation('title', $locale);
                        $bio = $item->getTranslation('content', $locale);
                        // Handle role as array or string
                        $roleData = $item->data['role'] ?? $item->data['role_' . $locale] ?? '';
                        $role = is_array($roleData) ? ($roleData[$locale] ?? $roleData[config('app.fallback_locale')] ?? '') : $roleData;
                        $itemImage = $item->getFirstMediaUrl('item_image');
                        $socialLinks = $item->data['social_links'] ?? [];
                        // Get profile link - either doctor profile or custom link
                        $doctorId = $item->data['doctor_id'] ?? null;
                        $customLink = $item->data['link'] ?? null;
                        $profileLink = $doctorId ? route('doctors.show', $doctorId) : $customLink;
                    @endphp
                    <div class="col-sm-6 col-lg-4 col-xl-3 panel-animate">
                        <div class="panel-team-card">
                            <div class="panel-team-avatar">
                                @if($itemImage)
                                    <img src="{{ $itemImage }}" alt="{{ $name }}" class="panel-avatar panel-avatar-lg">
                                @else
                                    <div class="panel-avatar panel-avatar-lg panel-avatar-placeholder">
                                        {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h5 class="panel-team-name">{{ $name }}</h5>
                            @if($role)
                                <p class="panel-team-role">{{ $role }}</p>
                            @endif
                            @if($bio)
                                <p class="panel-team-bio">{{ Str::limit($bio, 100) }}</p>
                            @endif
                            @if(is_array($socialLinks) && count($socialLinks) > 0)
                                <div class="panel-social-links">
                                    @foreach($socialLinks as $platform => $url)
                                        @if($url)
                                            <a href="{{ $url }}" target="_blank" rel="noopener" class="panel-social-link" title="{{ ucfirst($platform) }}">
                                                <i class="ti tabler-brand-{{ $platform }}"></i>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            @if($profileLink)
                                <a href="{{ $profileLink }}" class="panel-team-profile-link">
                                    <i class="ti ti-user"></i>
                                    {{ __('website::doctors.view_profile') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- View All Doctors Link --}}
            @if($showViewAllDoctors)
                <div class="text-center mt-5">
                    <a href="{{ route('doctors.index') }}" class="panel-btn panel-btn-primary">
                        <i class="ti ti-users"></i>
                        {{ __('website::doctors.view_all_doctors') }}
                    </a>
                </div>
            @endif
        @endif
    </div>
</section>
