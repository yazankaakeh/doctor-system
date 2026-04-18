@php
    if (!isset($panel)) {
        return;
    }
    $team = $panel;
    $teamBadge = $team['settings']['badge'][$locale] ?? trans('newLandingPage.howItWorksSection.titleSm');
    $teamTitle = $team['title'][$locale] ?? trans('newLandingPage.howItWorksSection.title1');
    $teamDescription = $team['settings']['description'][$locale] ?? trans('newLandingPage.howItWorksSection.title2');
    $teamMembers = $items ?? collect();
    $showViewAllDoctors = $team['settings']['show_view_all_doctors'] ?? false;
@endphp

<section id="theHow" class="section-py landing-team">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-label-primary">
                {{ $teamBadge }}
            </span>
        </div>
        <h4 class="text-center pb-4">
            <span class="position-relative fw-extrabold z-1">
                {{ $teamTitle }}
                <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="laptop charging"
                     class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            {{ $teamDescription }}
        </h4>
        <div class="row gy-12 mt-4">
            @foreach($teamMembers as $index => $member)
                @php
                    $doctorId = $member['data']['doctor_id'] ?? null;
                    $customLink = $member['data']['link'] ?? null;
                    $profileLink = $doctorId ? route('doctors.show', $doctorId) : $customLink;
                @endphp
                <div class="col-lg-3 col-sm-6">
                    <div class="card mt-3 mt-lg-0 shadow-none">
                        <div class="bg-label-info border border-bottom-0 border-label-info position-relative team-image-box">
                            <img src="{{ $member['media']['item_image'] ?? asset('assets/img/avatars/1.png') }}"
                                 class="position-absolute card-img-position bottom-0 start-50" alt="{{ $member['data']['name'] ?? $member['title'][$locale] ?? '' }}" />
                        </div>
                        <div class="card-body border border-top-0 border-label-info text-center">
                            <h5 class="card-title mb-0">{{ $member['data']['name'] ?? $member['title'][$locale] ?? '' }}</h5>
                            <p class="text-body-secondary mb-0">{{ $member['data']['role'][$locale] ?? $member['content'][$locale] ?? '' }}</p>
                            @if($profileLink)
                                <a href="{{ $profileLink }}" class="btn btn-sm btn-outline-primary mt-3">
                                    <i class="ti tabler-user me-1"></i>
                                    {{ __('website::doctors.view_profile') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- View All Doctors Link --}}
        @if($showViewAllDoctors)
            <div class="text-center mt-5">
                <a href="{{ route('doctors.index') }}" class="btn btn-primary btn-lg">
                    <i class="ti tabler-users me-2"></i>
                    {{ __('website::doctors.view_all_doctors') }}
                </a>
            </div>
        @endif
    </div>
</section>
