@props(['userType' => 'patient'])

<div class="social-login-buttons">
    <div class="divider my-4">
        <div class="divider-text">{{ trans('auth::auth.or') }}</div>
    </div>

    <div class="d-flex justify-content-center">
        <a href="{{ route('auth.social.redirect', ['provider' => 'google', 'user_type' => $userType]) }}"
           class="btn btn-icon btn-label-google-plus me-3">
            <i class="ti icon-base tabler-brand-google fs-5"></i>
        </a>

        <a href="{{ route('auth.social.redirect', ['provider' => 'facebook', 'user_type' => $userType]) }}"
           class="btn btn-icon btn-label-facebook me-3">
            <i class="ti icon-base tabler-brand-facebook"></i>
        </a>

        <a href="{{ route('auth.social.redirect', ['provider' => 'x', 'user_type' => $userType]) }}"
           class="btn btn-icon btn-label-twitter">
            <i class="ti icon-base tabler-brand-x fs-5"></i>
        </a>
    </div>
</div>
