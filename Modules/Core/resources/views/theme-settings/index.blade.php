@php
    $page = 'theme-settings';
@endphp

@extends('theme::user.layouts.horizontalLayout')

@section('title', trans('core::core.theme_settings.title'))

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'], 'build/modules/theme')
    <style>
        .color-preview {
            width: 50px;
            height: 38px;
            border-radius: 0.375rem;
            border: 1px solid #ddd;
            cursor: pointer;
        }
        .theme-preview-box {
            border: 2px solid #e0e0e0;
            border-radius: 0.5rem;
            padding: 1rem;
            min-height: 150px;
            background: var(--preview-bg, #f8f7fa);
        }
    </style>
@endsection

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-12">
                    <div class="card-header px-0 pt-0 mb-4">
                        <h4 class="fw-bold">{{ trans('core::core.theme_settings.title') }}</h4>
                        <p class="text-muted">{{ trans('core::core.theme_settings.subtitle') }}</p>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#admin-settings" role="tab">
                                <i class="ti tabler-settings me-1"></i> {{ trans('core::core.theme_settings.admin_theme') }}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#website-settings" role="tab">
                                <i class="ti tabler-world me-1"></i> {{ trans('core::core.theme_settings.website_theme') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Admin Theme Settings -->
                        <div class="tab-pane fade show active" id="admin-settings" role="tabpanel">
                            @include('core::theme-settings.partials.theme-form', ['settings' => $adminSettings, 'scope' => 'admin'])
                        </div>

                        <!-- Website Theme Settings -->
                        <div class="tab-pane fade" id="website-settings" role="tabpanel">
                            @include('core::theme-settings.partials.theme-form', ['settings' => $websiteSettings, 'scope' => 'website'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        // Color picker sync
        document.querySelectorAll('input[type="color"]').forEach(input => {
            const textInput = document.getElementById(input.id + '_text');
            if (textInput) {
                input.addEventListener('input', () => textInput.value = input.value);
                textInput.addEventListener('input', () => input.value = textInput.value);
            }
        });

        // Live preview update
        function updatePreview(scope) {
            const form = document.getElementById(`theme-form-${scope}`);
            const preview = document.getElementById(`preview-${scope}`);
            if (!preview) return;

            const primaryColor = form.querySelector('[name="primary_color"]').value;
            const bodyBg = form.querySelector('[name="body_bg"]').value;
            const cardBg = form.querySelector('[name="card_bg"]').value;
            const fontFamily = form.querySelector('[name="font_family"]').value;

            preview.style.setProperty('--preview-bg', bodyBg);
            preview.querySelector('.preview-card').style.background = cardBg;
            preview.querySelector('.preview-button').style.background = primaryColor;
            preview.style.fontFamily = `"${fontFamily}", sans-serif`;
        }

        // Attach preview update listeners
        ['admin', 'website'].forEach(scope => {
            const form = document.getElementById(`theme-form-${scope}`);
            if (form) {
                form.querySelectorAll('input[type="color"], input[type="text"], select').forEach(input => {
                    input.addEventListener('input', () => updatePreview(scope));
                });
            }
        });

        // Font import URL helper
        document.querySelectorAll('[id^="font_family_"]').forEach(select => {
            select.addEventListener('change', function() {
                const scope = this.id.split('_').pop();
                const importUrlInput = document.getElementById(`font_import_url_${scope}`);
                if (!importUrlInput) return;

                const fontUrls = {
                    'Public Sans': 'https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap',
                    'Inter': 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
                    'Roboto': 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
                    'Open Sans': 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap',
                    'Poppins': 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
                    'Cairo': 'https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap',
                    'Tajawal': 'https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap',
                    'Montserrat': 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap',
                    'Lato': 'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap',
                    'Raleway': 'https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap'
                };

                if (fontUrls[this.value] && !importUrlInput.value) {
                    importUrlInput.value = fontUrls[this.value];
                }
            });
        });
    </script>
@endsection