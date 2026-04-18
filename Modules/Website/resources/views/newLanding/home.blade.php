{{--@extends('customer.layouts.layoutFront')
@section('content')
  @includeIf('newLanding.panels.hero')
  @includeIf('newLanding.panels.features')
  @includeIf('newLanding.panels.landingReviews')
  @includeIf('newLanding.panels.landingTeam')
  @includeIf('newLanding.panels.landingFunFacts')
  @includeIf('newLanding.panels.landingFAQ')
  @includeIf('newLanding.panels.landingCTA')
  @includeIf('newLanding.panels.landingContact')
  --}}{{--<!-- Pricing plans: Start -->
  <section id="landingPricing" class="section-py bg-body landing-pricing">
    <div class="container">
      <div class="text-center mb-3 pb-1">
        <span class="badge bg-label-primary">Pricing Plans</span>
      </div>
      <h3 class="text-center mb-1">
            <span class="position-relative fw-bold z-1"
            >Tailored pricing plans
              <img
                src="../../assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
        designed for you
      </h3>
      <p class="text-center mb-4 pb-3">
        All plans include 40+ advanced tools and features to boost your product.<br />Choose the best plan to fit
        your needs.
      </p>
      <div class="text-center mb-5">
        <div class="position-relative d-inline-block pt-3 pt-md-0">
          <label class="switch switch-primary me-0">
            <span class="switch-label">Pay Monthly</span>
            <input type="checkbox" class="switch-input price-duration-toggler" checked />
            <span class="switch-toggle-slider">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
            <span class="switch-label">Pay Annual</span>
          </label>
          <div class="pricing-plans-item position-absolute d-flex">
            <img
              src="../../assets/img/front-pages/icons/pricing-plans-arrow.png"
              alt="pricing plans arrow"
              class="scaleX-n1-rtl" />
            <span class="fw-medium mt-2 ms-1"> Save 25%</span>
          </div>
        </div>
      </div>
      <div class="row gy-4 pt-lg-3">
        <!-- Basic Plan: Start -->
        <div class="col-xl-4 col-lg-6">
          <div class="card">
            <div class="card-header">
              <div class="text-center">
                <img
                  src="../../assets/img/front-pages/icons/paper-airplane.png"
                  alt="paper airplane icon"
                  class="mb-4 pb-2" />
                <h4 class="mb-1">Basic</h4>
                <div class="d-flex align-items-center justify-content-center">
                  <span class="price-monthly h1 text-primary fw-bold mb-0">$19</span>
                  <span class="price-yearly h1 text-primary fw-bold mb-0 d-none">$14</span>
                  <sub class="h6 text-muted mb-0 ms-1">/mo</sub>
                </div>
                <div class="position-relative pt-2">
                  <div class="price-yearly text-muted price-yearly-toggle d-none">$ 168 / year</div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled">
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Timeline
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Basic search
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Live chat widget
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Email marketing
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Custom Forms
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Traffic analytics
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Basic Support
                  </h5>
                </li>
              </ul>
              <div class="d-grid mt-4 pt-3">
                <a href="payment-page.html" class="btn btn-label-primary">Get Started</a>
              </div>
            </div>
          </div>
        </div>
        <!-- Basic Plan: End -->

        <!-- Favourite Plan: Start -->
        <div class="col-xl-4 col-lg-6">
          <div class="card border border-primary shadow-lg">
            <div class="card-header">
              <div class="text-center">
                <img src="../../assets/img/front-pages/icons/plane.png" alt="plane icon" class="mb-4 pb-2" />
                <h4 class="mb-1">Team</h4>
                <div class="d-flex align-items-center justify-content-center">
                  <span class="price-monthly h1 text-primary fw-bold mb-0">$29</span>
                  <span class="price-yearly h1 text-primary fw-bold mb-0 d-none">$22</span>
                  <sub class="h6 text-muted mb-0 ms-1">/mo</sub>
                </div>
                <div class="position-relative pt-2">
                  <div class="price-yearly text-muted price-yearly-toggle d-none">$ 264 / year</div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled">
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Everything in basic
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Timeline with database
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Advanced search
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Marketing automation
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Advanced chatbot
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Campaign management
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Collaboration tools
                  </h5>
                </li>
              </ul>
              <div class="d-grid mt-4 pt-3">
                <a href="payment-page.html" class="btn btn-primary">Get Started</a>
              </div>
            </div>
          </div>
        </div>
        <!-- Favourite Plan: End -->

        <!-- Standard Plan: Start -->
        <div class="col-xl-4 col-lg-6">
          <div class="card">
            <div class="card-header">
              <div class="text-center">
                <img
                  src="../../assets/img/front-pages/icons/shuttle-rocket.png"
                  alt="shuttle rocket icon"
                  class="mb-4 pb-2" />
                <h4 class="mb-1">Enterprise</h4>
                <div class="d-flex align-items-center justify-content-center">
                  <span class="price-monthly h1 text-primary fw-bold mb-0">$49</span>
                  <span class="price-yearly h1 text-primary fw-bold mb-0 d-none">$37</span>
                  <sub class="h6 text-muted mb-0 ms-1">/mo</sub>
                </div>
                <div class="position-relative pt-2">
                  <div class="price-yearly text-muted price-yearly-toggle d-none">$ 444 / year</div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <ul class="list-unstyled">
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Everything in premium
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Timeline with database
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Fuzzy search
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    A/B testing sanbox
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Custom permissions
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Social media automation
                  </h5>
                </li>
                <li>
                  <h5>
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-2"
                        ><i class="ti ti-check ti-xs"></i
                          ></span>
                    Sales automation tools
                  </h5>
                </li>
              </ul>
              <div class="d-grid mt-4 pt-3">
                <a href="payment-page.html" class="btn btn-label-primary">Get Started</a>
              </div>
            </div>
          </div>
        </div>
        <!-- Standard Plan: End -->
      </div>
    </div>
  </section>
  <!-- Pricing plans: End -->--}}{{--
@endsection
@section('after_js')

@endsection--}}
@php
    use Modules\Theme\Helpers\Helpers;$configData = Helpers::appClasses();
@endphp

@extends('theme::user.layouts.layoutFront')

@section('title', 'Tagiy')

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/fonts/fontawesome.scss', 'resources/assets/vendor/scss/pages/page-icons.scss'], 'build/modules/theme')
    @vite(['resources/assets/vendor/libs/nouislider/nouislider.scss', 'resources/assets/vendor/libs/swiper/swiper.scss'], 'build/modules/theme')
@endsection

<!-- Page Styles -->
@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/front-page-landing.scss'], 'build/modules/theme')
    @vite(['resources/assets/vendor/scss/pages/page-icons.scss'], 'build/modules/theme')
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/nouislider/nouislider.js', 'resources/assets/vendor/libs/swiper/swiper.js'], 'build/modules/theme')
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/front-page-landing.js'], 'build/modules/theme')
@endsection

@section('content')
    <div data-bs-spy="scroll" class="scrollspy-example">
        {{-- Always include Hero section from meta_data (backward compatibility) --}}
        @includeIf('website::newLanding.panels.hero')

        {{-- Dynamic panels from CMS --}}
        @if(isset($panelsData) && $panelsData->count() > 0)
            @foreach($panelsData as $panel)
                @php
                    $items = collect($panel['items'] ?? []);
                    $panelType = $panel['type'];
                @endphp

                @switch($panelType)
                    @case('features')
                        @include('website::newLanding.panels.features', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('team')
                        @include('website::newLanding.panels.landingTeam', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('reviews')
                        @include('website::newLanding.panels.landingReviews', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('faq')
                        @include('website::newLanding.panels.landingFAQ', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('cta')
                        @include('website::newLanding.panels.landingCTA', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('contact')
                        @include('website::newLanding.panels.landingContact', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('stats')
                        @include('website::newLanding.panels.landingFunFacts', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('gallery')
                        @include('website::newLanding.panels.gallery', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('carousel')
                        @include('website::newLanding.panels.carousel', ['panel' => $panel, 'items' => $items])
                        @break
                    @case('booking')
                        @include('website::newLanding.panels.booking', ['panel' => $panel])
                        @break
                    @case('custom')
                        @include('website::newLanding.panels.custom', ['panel' => $panel, 'items' => $items])
                        @break
                @endswitch
            @endforeach
        @else
            {{-- Fallback to static includes if no panels in database --}}
            @includeIf('website::newLanding.panels.features')
            @includeIf('website::newLanding.panels.whyTagiy')
            @includeIf('website::newLanding.panels.getNfc')
            @includeIf('website::newLanding.panels.landingTeam')
            @includeIf('website::newLanding.panels.getStarted')
            @includeIf('website::newLanding.panels.landingContact')
        @endif
        {{--<section id="landingReviews" class="section-py bg-body landing-reviews pb-0">
          <!-- What people say slider: Start -->
          <div class="container">
            <div class="row align-items-center gx-0 gy-4 g-lg-5 mb-5 pb-md-5">
              <div class="col-md-6 col-lg-5 col-xl-3">
                <div class="mb-4">
                  <span class="badge bg-label-primary">Real Customers Reviews</span>
                </div>
                <h4 class="mb-1">
                <span class="position-relative fw-extrabold z-1">What people say
                  <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="laptop charging"
                       class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
                </span>
                </h4>
                <p class="mb-5 mb-md-12">
                  See what our customers have to<br class="d-none d-xl-block" />
                  say about their experience.
                </p>
                <div class="landing-reviews-btns">
                  <button id="reviews-previous-btn" class="btn btn-icon btn-label-primary reviews-btn me-3" type="button">
                    <i class="icon-base ti tabler-chevron-left icon-md scaleX-n1-rtl"></i>
                  </button>
                  <button id="reviews-next-btn" class="btn btn-icon btn-label-primary reviews-btn" type="button">
                    <i class="icon-base ti tabler-chevron-right icon-md scaleX-n1-rtl"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6 col-lg-7 col-xl-9">
                <div class="swiper-reviews-carousel overflow-hidden">
                  <div class="swiper" id="swiper-reviews">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <div class="card h-100">
                          <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                            <div class="mb-4">
                              <img src="{{ asset('assets/img/front-pages/branding/logo-1.png') }}" alt="client logo"
                                   class="client-logo img-fluid" />
                            </div>
                            <p>“Vuexy is hands down the most useful front end Bootstrap theme I've ever used. I can't wait
                              to
                              use it again for my next project.”</p>
                            <div class="text-warning mb-4">
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                            <div class="d-flex align-items-center">
                              <div class="avatar me-3 avatar-sm">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                              </div>
                              <div>
                                <h6 class="mb-0">Cecilia Payne</h6>
                                <p class="small text-body-secondary mb-0">CEO of Airbnb</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="card h-100">
                          <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                            <div class="mb-4">
                              <img src="{{ asset('assets/img/front-pages/branding/logo-2.png') }}" alt="client logo"
                                   class="client-logo img-fluid" />
                            </div>
                            <p>“I've never used a theme as versatile and flexible as Vuexy. It's my go to for building
                              dashboard sites on almost any project.”</p>
                            <div class="text-warning mb-4">
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                            <div class="d-flex align-items-center">
                              <div class="avatar me-3 avatar-sm">
                                <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle" />
                              </div>
                              <div>
                                <h6 class="mb-0">Eugenia Moore</h6>
                                <p class="small text-body-secondary mb-0">Founder of Hubspot</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="card h-100">
                          <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                            <div class="mb-4">
                              <img src="{{ asset('assets/img/front-pages/branding/logo-3.png') }}" alt="client logo"
                                   class="client-logo img-fluid" />
                            </div>
                            <p>This template is really clean & well documented. The docs are really easy to understand and
                              it's always easy to find a screenshot from their website.</p>
                            <div class="text-warning mb-4">
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                            <div class="d-flex align-items-center">
                              <div class="avatar me-3 avatar-sm">
                                <img src="{{ asset('assets/img/avatars/3.png') }}" alt="Avatar" class="rounded-circle" />
                              </div>
                              <div>
                                <h6 class="mb-0">Curtis Fletcher</h6>
                                <p class="small text-body-secondary mb-0">Design Lead at Dribbble</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="card h-100">
                          <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                            <div class="mb-4">
                              <img src="{{ asset('assets/img/front-pages/branding/logo-4.png') }}" alt="client logo"
                                   class="client-logo img-fluid" />
                            </div>
                            <p>All the requirements for developers have been taken into consideration, so I’m able to build
                              any interface I want.</p>
                            <div class="text-warning mb-4">
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                            <div class="d-flex align-items-center">
                              <div class="avatar me-3 avatar-sm">
                                <img src="{{ asset('assets/img/avatars/4.png') }}" alt="Avatar" class="rounded-circle" />
                              </div>
                              <div>
                                <h6 class="mb-0">Sara Smith</h6>
                                <p class="small text-body-secondary mb-0">Founder of Continental</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="card h-100">
                          <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                            <div class="mb-4">
                              <img src="{{ asset('assets/img/front-pages/branding/logo-5.png') }}" alt="client logo"
                                   class="client-logo img-fluid" />
                            </div>
                            <p>“I've never used a theme as versatile and flexible as Vuexy. It's my go to for building
                              dashboard sites on almost any project.”</p>
                            <div class="text-warning mb-4">
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                            <div class="d-flex align-items-center">
                              <div class="avatar me-3 avatar-sm">
                                <img src="{{ asset('assets/img/avatars/5.png') }}" alt="Avatar" class="rounded-circle" />
                              </div>
                              <div>
                                <h6 class="mb-0">Eugenia Moore</h6>
                                <p class="small text-body-secondary mb-0">Founder of Hubspot</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="swiper-slide">
                        <div class="card h-100">
                          <div class="card-body text-body d-flex flex-column justify-content-between h-100">
                            <div class="mb-4">
                              <img src="{{ asset('assets/img/front-pages/branding/logo-6.png') }}" alt="client logo"
                                   class="client-logo img-fluid" />
                            </div>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Veniam nemo mollitia, ad eum officia
                              numquam nostrum repellendus consequuntur!</p>
                            <div class="text-warning mb-4">
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                              <i class="icon-base ti tabler-star-filled"></i>
                            </div>
                            <div class="d-flex align-items-center">
                              <div class="avatar me-3 avatar-sm">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                              </div>
                              <div>
                                <h6 class="mb-0">Sara Smith</h6>
                                <p class="small text-body-secondary mb-0">Founder of Continental</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- What people say slider: End -->
          <hr class="m-0 mt-6 mt-md-12" />
          <!-- Logo slider: Start -->
          <div class="container">
            <div class="swiper-logo-carousel pt-8">
              <div class="swiper" id="swiper-clients-logos">
                <div class="swiper-wrapper">
                  <div class="swiper-slide">
                    <img src="{{ asset('assets/img/front-pages/branding/logo_1-' . $configData['theme'] . '.png') }}"
                         alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_1-light.png"
                         data-app-dark-img="front-pages/branding/logo_1-dark.png" />
                  </div>
                  <div class="swiper-slide">
                    <img src="{{ asset('assets/img/front-pages/branding/logo_2-' . $configData['theme'] . '.png') }}"
                         alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_2-light.png"
                         data-app-dark-img="front-pages/branding/logo_2-dark.png" />
                  </div>
                  <div class="swiper-slide">
                    <img src="{{ asset('assets/img/front-pages/branding/logo_3-' . $configData['theme'] . '.png') }}"
                         alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_3-light.png"
                         data-app-dark-img="front-pages/branding/logo_3-dark.png" />
                  </div>
                  <div class="swiper-slide">
                    <img src="{{ asset('assets/img/front-pages/branding/logo_4-' . $configData['theme'] . '.png') }}"
                         alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_4-light.png"
                         data-app-dark-img="front-pages/branding/logo_4-dark.png" />
                  </div>
                  <div class="swiper-slide">
                    <img src="{{ asset('assets/img/front-pages/branding/logo_5-' . $configData['theme'] . '.png') }}"
                         alt="client logo" class="client-logo" data-app-light-img="front-pages/branding/logo_5-light.png"
                         data-app-dark-img="front-pages/branding/logo_5-dark.png" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Logo slider: End -->
        </section>
        <!-- Real customers reviews: End -->


        <!-- Pricing plans: Start -->
        <section id="landingPricing" class="section-py bg-body landing-pricing">
          <div class="container">
            <div class="text-center mb-4">
              <span class="badge bg-label-primary">Pricing Plans</span>
            </div>
            <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1">Tailored pricing plans
              <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="laptop charging"
                   class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
              designed for you
            </h4>
            <p class="text-center pb-2 mb-7">All plans include 40+ advanced tools and features to boost your
              product.<br />Choose the best plan to fit your needs.</p>
            <div class="text-center mb-12">
              <div class="position-relative d-inline-block pt-3 pt-md-0">
                <label class="switch switch-sm switch-primary me-0">
                  <span class="switch-label fs-6 text-body me-3">Pay Monthly</span>
                  <input type="checkbox" class="switch-input price-duration-toggler" checked />
                  <span class="switch-toggle-slider">
                  <span class="switch-on"></span>
                  <span class="switch-off"></span>
                </span>
                  <span class="switch-label fs-6 text-body ms-3">Pay Annual</span>
                </label>
                <div class="pricing-plans-item position-absolute d-flex">
                  <img src="{{ asset('assets/img/front-pages/icons/pricing-plans-arrow.png') }}" alt="pricing plans arrow"
                       class="scaleX-n1-rtl" />
                  <span class="fw-medium mt-2 ms-1"> Save 25%</span>
                </div>
              </div>
            </div>
            <div class="row g-6 pt-lg-5">
              <!-- Basic Plan: Start -->
              <div class="col-xl-4 col-lg-6">
                <div class="card">
                  <div class="card-header">
                    <div class="text-center">
                      <img src="{{ asset('assets/img/front-pages/icons/paper-airplane.png') }}" alt="paper airplane icon"
                           class="mb-8 pb-2" />
                      <h4 class="mb-0">Basic</h4>
                      <div class="d-flex align-items-center justify-content-center">
                        <span class="price-monthly h2 text-primary fw-extrabold mb-0">$19</span>
                        <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">$14</span>
                        <sub class="h6 text-body-secondary mb-n1 ms-1">/mo</sub>
                      </div>
                      <div class="position-relative pt-2">
                        <div class="price-yearly text-body-secondary price-yearly-toggle d-none">$ 168 / year</div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <ul class="list-unstyled pricing-list">
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Timeline
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Basic search
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Live chat widget
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Email marketing
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Custom Forms
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Traffic analytics
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Basic Support
                        </h6>
                      </li>
                    </ul>
                    <div class="d-grid mt-8">
                      <a href="{{ url('/front-pages/payment') }}" class="btn btn-label-primary">Get Started</a>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Basic Plan: End -->

              <!-- Favourite Plan: Start -->
              <div class="col-xl-4 col-lg-6">
                <div class="card border border-primary shadow-xl">
                  <div class="card-header">
                    <div class="text-center">
                      <img src="{{ asset('assets/img/front-pages/icons/plane.png') }}" alt="plane icon" class="mb-8 pb-2" />
                      <h4 class="mb-0">Team</h4>
                      <div class="d-flex align-items-center justify-content-center">
                        <span class="price-monthly h2 text-primary fw-extrabold mb-0">$29</span>
                        <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">$22</span>
                        <sub class="h6 text-body-secondary mb-n1 ms-1">/mo</sub>
                      </div>
                      <div class="position-relative pt-2">
                        <div class="price-yearly text-body-secondary price-yearly-toggle d-none">$ 264 / year</div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <ul class="list-unstyled pricing-list">
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Everything in basic
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Timeline with database
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Advanced search
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Marketing automation
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Advanced chatbot
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Campaign management
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Collaboration tools
                        </h6>
                      </li>
                    </ul>
                    <div class="d-grid mt-8">
                      <a href="{{ url('/front-pages/payment') }}" class="btn btn-primary">Get Started</a>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Favourite Plan: End -->

              <!-- Standard Plan: Start -->
              <div class="col-xl-4 col-lg-6">
                <div class="card">
                  <div class="card-header">
                    <div class="text-center">
                      <img src="{{ asset('assets/img/front-pages/icons/shuttle-rocket.png') }}" alt="shuttle rocket icon"
                           class="mb-8 pb-2" />
                      <h4 class="mb-0">Enterprise</h4>
                      <div class="d-flex align-items-center justify-content-center">
                        <span class="price-monthly h2 text-primary fw-extrabold mb-0">$49</span>
                        <span class="price-yearly h2 text-primary fw-extrabold mb-0 d-none">$37</span>
                        <sub class="h6 text-body-secondary mb-n1 ms-1">/mo</sub>
                      </div>
                      <div class="position-relative pt-2">
                        <div class="price-yearly text-body-secondary price-yearly-toggle d-none">$ 444 / year</div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <ul class="list-unstyled pricing-list">
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Everything in premium
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Timeline with database
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Fuzzy search
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          A/B testing sanbox
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Custom permissions
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Social media automation
                        </h6>
                      </li>
                      <li>
                        <h6 class="d-flex align-items-center mb-3">
                        <span class="badge badge-center rounded-pill bg-label-primary p-0 me-3"><i
                            class="icon-base ti tabler-check icon-12px"></i></span>
                          Sales automation tools
                        </h6>
                      </li>
                    </ul>
                    <div class="d-grid mt-8">
                      <a href="{{ url('/front-pages/payment') }}" class="btn btn-label-primary">Get Started</a>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Standard Plan: End -->
            </div>
          </div>
        </section>
        <!-- Pricing plans: End -->

        <!-- Fun facts: Start -->
        <section id="landingFunFacts" class="section-py landing-fun-facts">
          <div class="container">
            <div class="row gy-6">
              <div class="col-sm-6 col-lg-3">
                <div class="card border border-primary shadow-none">
                  <div class="card-body text-center">
                    <div class="mb-4 text-primary">
                      <svg width="64" height="65" viewBox="0 0 64 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.2"
                              d="M10 44.4663V18.4663C10 17.4054 10.4214 16.388 11.1716 15.6379C11.9217 14.8877 12.9391 14.4663 14 14.4663H50C51.0609 14.4663 52.0783 14.8877 52.8284 15.6379C53.5786 16.388 54 17.4054 54 18.4663V44.4663H10Z"
                              fill="currentColor" />
                        <path
                          d="M10 44.4663V18.4663C10 17.4054 10.4214 16.388 11.1716 15.6379C11.9217 14.8877 12.9391 14.4663 14 14.4663H50C51.0609 14.4663 52.0783 14.8877 52.8284 15.6379C53.5786 16.388 54 17.4054 54 18.4663V44.4663M36 22.4663H28M6 44.4663H58V48.4663C58 49.5272 57.5786 50.5446 56.8284 51.2947C56.0783 52.0449 55.0609 52.4663 54 52.4663H10C8.93913 52.4663 7.92172 52.0449 7.17157 51.2947C6.42143 50.5446 6 49.5272 6 48.4663V44.4663Z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                    <h3 class="mb-0">7.1k+</h3>
                    <p class="fw-medium mb-0">
                      Support Tickets<br />
                      Resolved
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-3">
                <div class="card border border-success shadow-none">
                  <div class="card-body text-center">
                    <div class="mb-4 text-success">
                      <svg width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="User">
                          <path id="Vector" opacity="0.2"
                                d="M32.4999 8.52881C27.6437 8.52739 22.9012 9.99922 18.899 12.7499C14.8969 15.5005 11.8233 19.4006 10.0844 23.9348C8.34542 28.4691 8.02291 33.4242 9.15945 38.1456C10.296 42.867 12.8381 47.1326 16.4499 50.3788C17.9549 47.4151 20.2511 44.9261 23.0841 43.1875C25.917 41.4489 29.176 40.5287 32.4999 40.5288C30.5221 40.5288 28.5887 39.9423 26.9442 38.8435C25.2997 37.7447 24.018 36.1829 23.2611 34.3556C22.5043 32.5284 22.3062 30.5177 22.6921 28.5779C23.0779 26.6381 24.0303 24.8563 25.4289 23.4577C26.8274 22.0592 28.6092 21.1068 30.549 20.721C32.4888 20.3351 34.4995 20.5331 36.3268 21.29C38.154 22.0469 39.7158 23.3286 40.8146 24.9731C41.9135 26.6176 42.4999 28.551 42.4999 30.5288C42.4999 33.181 41.4464 35.7245 39.571 37.5999C37.6956 39.4752 35.1521 40.5288 32.4999 40.5288C35.8238 40.5287 39.0829 41.4489 41.9158 43.1875C44.7487 44.9261 47.045 47.4151 48.5499 50.3788C52.1618 47.1326 54.7039 42.867 55.8404 38.1456C56.977 33.4242 56.6545 28.4691 54.9155 23.9348C53.1766 19.4006 50.103 15.5005 46.1008 12.7499C42.0987 9.99922 37.3562 8.52739 32.4999 8.52881Z"
                                fill="currentColor" />
                          <path id="Vector_2"
                                d="M32.5 40.5288C38.0228 40.5288 42.5 36.0517 42.5 30.5288C42.5 25.006 38.0228 20.5288 32.5 20.5288C26.9772 20.5288 22.5 25.006 22.5 30.5288C22.5 36.0517 26.9772 40.5288 32.5 40.5288ZM32.5 40.5288C29.1759 40.5288 25.9168 41.4477 23.0839 43.1866C20.2509 44.9255 17.9548 47.4149 16.45 50.3788M32.5 40.5288C35.8241 40.5288 39.0832 41.4477 41.9161 43.1866C44.7491 44.9255 47.0452 47.4149 48.55 50.3788M56.5 32.5288C56.5 45.7836 45.7548 56.5288 32.5 56.5288C19.2452 56.5288 8.5 45.7836 8.5 32.5288C8.5 19.274 19.2452 8.52881 32.5 8.52881C45.7548 8.52881 56.5 19.274 56.5 32.5288Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                      </svg>
                    </div>
                    <h3 class="mb-0">50k+</h3>
                    <p class="fw-medium mb-0">
                      Join creatives<br />
                      community
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-3">
                <div class="card border border-info shadow-none">
                  <div class="card-body text-center">
                    <div class="mb-4 text-info">
                      <svg width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.2"
                              d="M46.5001 10.5288H32.5001L20.2251 26.5288L32.5001 56.5288L60.5001 26.5288L46.5001 10.5288Z"
                              fill="currentColor" />
                        <path d="M18.5 10.5288H46.5L60.5 26.5288L32.5 56.5288L4.5 26.5288L18.5 10.5288Z"
                              stroke="currentColor"
                              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M33.2934 9.92012C33.1042 9.67343 32.8109 9.52881 32.5 9.52881C32.1891 9.52881 31.8958 9.67343 31.7066 9.92012L19.7318 25.5288H4.5C3.94772 25.5288 3.5 25.9765 3.5 26.5288C3.5 27.0811 3.94772 27.5288 4.5 27.5288H19.5537L31.5745 56.9075C31.7282 57.2833 32.094 57.5288 32.5 57.5288C32.906 57.5288 33.2718 57.2833 33.4255 56.9075L45.4463 27.5288H60.5C61.0523 27.5288 61.5 27.0811 61.5 26.5288C61.5 25.9765 61.0523 25.5288 60.5 25.5288H45.2682L33.2934 9.92012ZM42.7474 25.5288L32.5 12.1717L22.2526 25.5288H42.7474ZM21.7146 27.5288L32.5 53.8881L43.2854 27.5288H21.7146Z"
                              fill="currentColor" />
                      </svg>
                    </div>
                    <h3 class="mb-0">4.8/5</h3>
                    <p class="fw-medium mb-0">
                      Highly Rated<br />
                      Products
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-3">
                <div class="card border border-warning shadow-none">
                  <div class="card-body text-center">
                    <div class="mb-4 text-warning">
                      <svg width="65" height="65" viewBox="0 0 65 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.2"
                              d="M14.125 50.9038C11.825 48.6038 13.35 43.7788 12.175 40.9538C11 38.1288 6.5 35.6538 6.5 32.5288C6.5 29.4038 10.95 27.0288 12.175 24.1038C13.4 21.1788 11.825 16.4538 14.125 14.1538C16.425 11.8538 21.25 13.3788 24.075 12.2038C26.9 11.0288 29.375 6.52881 32.5 6.52881C35.625 6.52881 38 10.9788 40.925 12.2038C43.85 13.4288 48.575 11.8538 50.875 14.1538C53.175 16.4538 51.65 21.2788 52.825 24.1038C54 26.9288 58.5 29.4038 58.5 32.5288C58.5 35.6538 54.05 38.0288 52.825 40.9538C51.6 43.8788 53.175 48.6038 50.875 50.9038C48.575 53.2038 43.75 51.6788 40.925 52.8538C38.1 54.0288 35.625 58.5288 32.5 58.5288C29.375 58.5288 27 54.0788 24.075 52.8538C21.15 51.6288 16.425 53.2038 14.125 50.9038Z"
                              fill="currentColor" />
                        <path
                          d="M43.5 26.5288L28.825 40.5288L21.5 33.5288M14.125 50.9038C11.825 48.6038 13.35 43.7788 12.175 40.9538C11 38.1288 6.5 35.6538 6.5 32.5288C6.5 29.4038 10.95 27.0288 12.175 24.1038C13.4 21.1788 11.825 16.4538 14.125 14.1538C16.425 11.8538 21.25 13.3788 24.075 12.2038C26.9 11.0288 29.375 6.52881 32.5 6.52881C35.625 6.52881 38 10.9788 40.925 12.2038C43.85 13.4288 48.575 11.8538 50.875 14.1538C53.175 16.4538 51.65 21.2788 52.825 24.1038C54 26.9288 58.5 29.4038 58.5 32.5288C58.5 35.6538 54.05 38.0288 52.825 40.9538C51.6 43.8788 53.175 48.6038 50.875 50.9038C48.575 53.2038 43.75 51.6788 40.925 52.8538C38.1 54.0288 35.625 58.5288 32.5 58.5288C29.375 58.5288 27 54.0788 24.075 52.8538C21.15 51.6288 16.425 53.2038 14.125 50.9038Z"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </div>
                    <h3 class="mb-0">100%</h3>
                    <p class="fw-medium mb-0">
                      Money Back<br />
                      Guarantee
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- Fun facts: End -->

        <!-- FAQ: Start -->
        <section id="landingFAQ" class="section-py bg-body landing-faq">
          <div class="container">
            <div class="text-center mb-4">
              <span class="badge bg-label-primary">FAQ</span>
            </div>
            <h4 class="text-center mb-1">
              Frequently asked
              <span class="position-relative fw-extrabold z-1">questions
              <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="laptop charging"
                   class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
            </h4>
            <p class="text-center mb-12 pb-md-4">Browse through these FAQs to find answers to commonly asked questions.</p>
            <div class="row gy-12 align-items-center">
              <div class="col-lg-5">
                <div class="text-center">
                  <img src="{{ asset('assets/img/front-pages/landing-page/faq-boy-with-logos.png') }}"
                       alt="faq boy with logos" class="faq-image" />
                </div>
              </div>
              <div class="col-lg-7">
                <div class="accordion" id="accordionExample">
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button type="button" class="accordion-button" data-bs-toggle="collapse"
                              data-bs-target="#accordionOne"
                              aria-expanded="true" aria-controls="accordionOne">Do you charge for each upgrade?
                      </button>
                    </h2>

                    <div id="accordionOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                      <div class="accordion-body">Lemon drops chocolate cake gummies carrot cake chupa chups muffin topping.
                        Sesame snaps icing marzipan gummi bears macaroon dragée danish caramels powder. Bear claw dragée
                        pastry topping soufflé. Wafer gummi bears marshmallow pastry pie.
                      </div>
                    </div>
                  </div>
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                      <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                              data-bs-target="#accordionTwo" aria-expanded="false" aria-controls="accordionTwo">Do I need to
                        purchase a license for each website?
                      </button>
                    </h2>
                    <div id="accordionTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                         data-bs-parent="#accordionExample">
                      <div class="accordion-body">Dessert ice cream donut oat cake jelly-o pie sugar plum cheesecake. Bear
                        claw dragée oat cake dragée ice cream halvah tootsie roll. Danish cake oat cake pie macaroon tart
                        donut gummies. Jelly beans candy canes carrot cake. Fruitcake chocolate chupa chups.
                      </div>
                    </div>
                  </div>
                  <div class="card accordion-item active">
                    <h2 class="accordion-header" id="headingThree">
                      <button type="button" class="accordion-button" data-bs-toggle="collapse"
                              data-bs-target="#accordionThree" aria-expanded="false" aria-controls="accordionThree">What is
                        regular
                        license?
                      </button>
                    </h2>
                    <div id="accordionThree" class="accordion-collapse collapse show" aria-labelledby="headingThree"
                         data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        Regular license can be used for end products that do not charge users for access or service(access
                        is
                        free and there will be no monthly subscription fee). Single regular license can be used for single
                        end
                        product and end product can be used by you or your client. If you want to sell end product to
                        multiple
                        clients then you will need to purchase separate license for each client. The same rule applies if
                        you
                        want to use the same end product on multiple domains(unique setup).
                        For more info on regular license you can check official description.
                      </div>
                    </div>
                  </div>
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                      <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                              data-bs-target="#accordionFour" aria-expanded="false" aria-controls="accordionFour">What is
                        extended
                        license?
                      </button>
                    </h2>
                    <div id="accordionFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                         data-bs-parent="#accordionExample">
                      <div class="accordion-body">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nobis et aliquid
                        quaerat possimus maxime! Mollitia reprehenderit neque repellat deleniti delectus architecto dolorum
                        maxime, blanditiis earum ea, incidunt quam possimus cumque.
                      </div>
                    </div>
                  </div>
                  <div class="card accordion-item">
                    <h2 class="accordion-header" id="headingFive">
                      <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse"
                              data-bs-target="#accordionFive" aria-expanded="false" aria-controls="accordionFive">Which
                        license is
                        applicable for SASS application?
                      </button>
                    </h2>
                    <div id="accordionFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                         data-bs-parent="#accordionExample">
                      <div class="accordion-body">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Sequi molestias
                        exercitationem ab cum nemo facere voluptates veritatis quia, eveniet veniam at et repudiandae
                        mollitia
                        ipsam quasi labore enim architecto non!
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- FAQ: End -->

        <!-- CTA: Start -->
        <section id="landingCTA" class="section-py landing-cta position-relative p-lg-0 pb-0">
          <img src="{{ asset('assets/img/front-pages/backgrounds/cta-bg-' . $configData['theme'] . '.png') }}"
               class="position-absolute bottom-0 end-0 scaleX-n1-rtl h-100 w-100 z-n1" alt="cta image"
               data-app-light-img="front-pages/backgrounds/cta-bg-light.png"
               data-app-dark-img="front-pages/backgrounds/cta-bg-dark.png" />
          <div class="container">
            <div class="row align-items-center gy-12">
              <div class="col-lg-6 text-start text-sm-center text-lg-start">
                <h3 class="cta-title text-primary fw-bold mb-1">Ready to Get Started?</h3>
                <h5 class="text-body mb-8">Start your project with a 14-day free trial</h5>
                <a href="{{ url('/front-pages/payment') }}" class="btn btn-lg btn-primary">Get Started</a>
              </div>
              <div class="col-lg-6 pt-lg-12 text-center text-lg-end">
                <img src="{{ asset('assets/img/front-pages/landing-page/cta-dashboard.png') }}" alt="cta dashboard"
                     class="img-fluid mt-lg-4" />
              </div>
            </div>
          </div>
        </section>
        <!-- CTA: End -->

        <!-- Contact Us: Start -->
        <section id="landingContact" class="section-py bg-body landing-contact">
          <div class="container">
            <div class="text-center mb-4">
              <span class="badge bg-label-primary">Contact US</span>
            </div>
            <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1">Let's work
              <img src="{{ asset('assets/img/front-pages/icons/section-title-icon.png') }}" alt="laptop charging"
                   class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
              together
            </h4>
            <p class="text-center mb-12 pb-md-4">Any question or remark? just write us a message</p>
            <div class="row g-6">
              <div class="col-lg-5">
                <div class="contact-img-box position-relative border p-2 h-100">
                  <img src="{{ asset('assets/img/front-pages/icons/contact-border.png') }}" alt="contact border"
                       class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
                  <img src="{{ asset('assets/img/front-pages/landing-page/contact-customer-service.png') }}"
                       alt="contact customer service" class="contact-img w-100 scaleX-n1-rtl" />
                  <div class="p-4 pb-2">
                    <div class="row g-4">
                      <div class="col-md-6 col-lg-12 col-xl-6">
                        <div class="d-flex align-items-center">
                          <div class="badge bg-label-primary rounded p-1_5 me-3"><i
                              class="icon-base ti tabler-mail icon-lg"></i></div>
                          <div>
                            <p class="mb-0">Email</p>
                            <h6 class="mb-0"><a href="mailto:example@gmail.com" class="text-heading">example@gmail.com</a>
                            </h6>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6 col-lg-12 col-xl-6">
                        <div class="d-flex align-items-center">
                          <div class="badge bg-label-success rounded p-1_5 me-3"><i
                              class="icon-base ti tabler-phone-call icon-lg"></i></div>
                          <div>
                            <p class="mb-0">Phone</p>
                            <h6 class="mb-0"><a href="tel:+1234-568-963" class="text-heading">+1234 568 963</a></h6>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-7">
                <div class="card h-100">
                  <div class="card-body">
                    <h4 class="mb-2">Send a message</h4>
                    <p class="mb-6">
                      If you would like to discuss anything related to payment, account, licensing,<br
                        class="d-none d-lg-block" />
                      partnerships, or have pre-sales questions, you’re at the right place.
                    </p>
                    <form>
                      <div class="row g-4">
                        <div class="col-md-6">
                          <label class="form-label" for="contact-form-fullname">Full Name</label>
                          <input type="text" class="form-control" id="contact-form-fullname" placeholder="john" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label" for="contact-form-email">Email</label>
                          <input type="text" id="contact-form-email" class="form-control" placeholder="johndoe@gmail.com" />
                        </div>
                        <div class="col-12">
                          <label class="form-label" for="contact-form-message">Message</label>
                          <textarea id="contact-form-message" class="form-control" rows="7"
                                    placeholder="Write a message"></textarea>
                        </div>
                        <div class="col-12">
                          <button type="submit" class="btn btn-primary">Send inquiry</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- Contact Us: End -->--}}
    </div>
@endsection
