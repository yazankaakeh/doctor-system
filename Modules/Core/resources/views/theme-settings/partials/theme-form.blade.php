<form action="{{ route('doctor.theme.settings.update') }}" method="POST" enctype="multipart/form-data" id="theme-form-{{ $scope }}">
    @csrf
    <input type="hidden" name="scope" value="{{ $scope }}">

    <div class="row">
        <!-- Left Column - Settings -->
        <div class="col-lg-8">
            <!-- Light Mode Colors Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti tabler-sun me-2"></i>{{ trans('core::core.theme_settings.colors') }} (Light Mode)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(['primary', 'secondary', 'success', 'info', 'warning', 'danger'] as $colorType)
                            <div class="col-md-6">
                                <label class="form-label">{{ trans("core::core.theme_settings.{$colorType}_color") }}</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="{{ $colorType }}_color_{{ $scope }}"
                                           name="{{ $colorType }}_color"
                                           value="{{ old("{$colorType}_color", $settings->{$colorType.'_color'}) }}">
                                    <input type="text" class="form-control"
                                           id="{{ $colorType }}_color_{{ $scope }}_text"
                                           value="{{ old("{$colorType}_color", $settings->{$colorType.'_color'}) }}"
                                           pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Dark Mode Colors Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti tabler-moon me-2"></i>{{ trans('core::core.theme_settings.colors') }} (Dark Mode)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(['primary', 'secondary', 'success', 'info', 'warning', 'danger'] as $colorType)
                            <div class="col-md-6">
                                <label class="form-label">{{ trans("core::core.theme_settings.{$colorType}_color") }}</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color"
                                           id="dark_{{ $colorType }}_color_{{ $scope }}"
                                           name="dark_{{ $colorType }}_color"
                                           value="{{ old("dark_{$colorType}_color", $settings->{'dark_'.$colorType.'_color'}) }}">
                                    <input type="text" class="form-control"
                                           id="dark_{{ $colorType }}_color_{{ $scope }}_text"
                                           value="{{ old("dark_{$colorType}_color", $settings->{'dark_'.$colorType.'_color'}) }}"
                                           pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Typography Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti tabler-typography me-2"></i>{{ trans('core::core.theme_settings.typography') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-core::select
                                :label="trans('core::core.theme_settings.font_family')"
                                name="font_family"
                                id="font_family_{{ $scope }}"
                                :options="['Public Sans' => 'Public Sans', 'Inter' => 'Inter', 'Roboto' => 'Roboto', 'Open Sans' => 'Open Sans', 'Poppins' => 'Poppins', 'Cairo' => 'Cairo (Arabic)', 'Tajawal' => 'Tajawal (Arabic)', 'Montserrat' => 'Montserrat', 'Lato' => 'Lato', 'Raleway' => 'Raleway']"
                                value="{{ old('font_family', $settings->font_family) }}">
                            </x-core::select>
                        </div>
                        <div class="col-md-6">
                            <x-core::input
                                :label="trans('core::core.theme_settings.font_size_base')"
                                type="text"
                                name="font_size_base"
                                id="font_size_base_{{ $scope }}"
                                value="{{ old('font_size_base', $settings->font_size_base) }}">
                            </x-core::input>
                            <small class="text-muted">e.g., 0.9375rem or 15px</small>
                        </div>
                        <div class="col-12">
                            <x-core::input
                                :label="trans('core::core.theme_settings.font_import_url')"
                                type="url"
                                name="font_import_url"
                                id="font_import_url_{{ $scope }}"
                                placeholder="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
                                value="{{ old('font_import_url', $settings->font_import_url) }}">
                            </x-core::input>
                            <small class="text-muted">
                                <i class="ti tabler-info-circle"></i> Import Google Fonts or custom font URLs.
                                <a href="https://fonts.google.com/" target="_blank" class="text-primary">Browse Google Fonts</a>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <x-core::input
                                :label="trans('core::core.theme_settings.headings_font_family')"
                                type="text"
                                name="headings_font_family"
                                id="headings_font_family_{{ $scope }}"
                                placeholder="Leave empty to use base font"
                                value="{{ old('headings_font_family', $settings->headings_font_family) }}">
                            </x-core::input>
                        </div>
                        <div class="col-md-6">
                            <x-core::select
                                :label="trans('core::core.theme_settings.headings_font_weight')"
                                name="headings_font_weight"
                                id="headings_font_weight_{{ $scope }}"
                                :options="['300' => 'Light (300)', '400' => 'Normal (400)', '500' => 'Medium (500)', '600' => 'Semi Bold (600)', '700' => 'Bold (700)']"
                                value="{{ old('headings_font_weight', $settings->headings_font_weight) }}">
                            </x-core::select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                <i class="ti tabler-file-upload me-1"></i>{{ trans('core::core.theme_settings.custom_fonts') }}
                            </label>
                            <textarea
                                name="custom_fonts"
                                id="custom_fonts_{{ $scope }}"
                                class="form-control font-monospace"
                                rows="6"
                                placeholder="@font-face {&#10;  font-family: 'MyCustomFont';&#10;  src: url('/fonts/MyCustomFont.woff2') format('woff2');&#10;  font-weight: normal;&#10;  font-style: normal;&#10;}">{{ old('custom_fonts', is_array($settings->custom_fonts) ? implode("\n---FONT-SEPARATOR---\n", $settings->custom_fonts) : '') }}</textarea>
                            <small class="text-muted">
                                <i class="ti tabler-info-circle"></i> Add custom @font-face declarations for uploaded fonts.
                                Separate multiple font declarations with <code>---FONT-SEPARATOR---</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti tabler-layout me-2"></i>{{ trans('core::core.theme_settings.layout') }}</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Light Mode</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('core::core.theme_settings.body_bg') }}</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color"
                                       id="body_bg_{{ $scope }}" name="body_bg"
                                       value="{{ old('body_bg', $settings->body_bg) }}">
                                <input type="text" class="form-control" id="body_bg_{{ $scope }}_text"
                                       value="{{ old('body_bg', $settings->body_bg) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('core::core.theme_settings.card_bg') }}</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color"
                                       id="card_bg_{{ $scope }}" name="card_bg"
                                       value="{{ old('card_bg', $settings->card_bg) }}">
                                <input type="text" class="form-control" id="card_bg_{{ $scope }}_text"
                                       value="{{ old('card_bg', $settings->card_bg) }}">
                            </div>
                        </div>
                    </div>

                    <h6 class="mb-3">Dark Mode</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('core::core.theme_settings.body_bg') }} (Dark)</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color"
                                       id="dark_body_bg_{{ $scope }}" name="dark_body_bg"
                                       value="{{ old('dark_body_bg', $settings->dark_body_bg) }}">
                                <input type="text" class="form-control" id="dark_body_bg_{{ $scope }}_text"
                                       value="{{ old('dark_body_bg', $settings->dark_body_bg) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ trans('core::core.theme_settings.card_bg') }} (Dark)</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color"
                                       id="dark_card_bg_{{ $scope }}" name="dark_card_bg"
                                       value="{{ old('dark_card_bg', $settings->dark_card_bg) }}">
                                <input type="text" class="form-control" id="dark_card_bg_{{ $scope }}_text"
                                       value="{{ old('dark_card_bg', $settings->dark_card_bg) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <x-core::input
                                :label="trans('core::core.theme_settings.border_radius')"
                                type="text"
                                name="border_radius"
                                id="border_radius_{{ $scope }}"
                                value="{{ old('border_radius', $settings->border_radius) }}">
                            </x-core::input>
                            <small class="text-muted">e.g., 0.375rem or 6px</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti tabler-brand-tabler me-2"></i>{{ trans('core::core.theme_settings.branding') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <x-core::input
                                :label="trans('core::core.theme_settings.site_title')"
                                type="text"
                                name="site_title"
                                id="site_title_{{ $scope }}"
                                value="{{ old('site_title', $settings->site_title) }}">
                            </x-core::input>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('core::core.theme_settings.logo') }}</label>
                            @if($settings->getFirstMediaUrl('logo'))
                                <div class="mb-2">
                                    <img src="{{ $settings->getFirstMediaUrl('logo') }}" alt="Logo" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="logo" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('core::core.theme_settings.logo_dark') }}</label>
                            @if($settings->getFirstMediaUrl('logo_dark'))
                                <div class="mb-2">
                                    <img src="{{ $settings->getFirstMediaUrl('logo_dark') }}" alt="Dark Logo" class="img-thumbnail" style="max-height: 80px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="logo_dark" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ trans('core::core.theme_settings.favicon') }}</label>
                            @if($settings->getFirstMediaUrl('favicon'))
                                <div class="mb-2">
                                    <img src="{{ $settings->getFirstMediaUrl('favicon') }}" alt="Favicon" class="img-thumbnail" style="max-height: 32px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="favicon" accept=".ico,.png">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti tabler-code me-2"></i>{{ trans('core::core.theme_settings.advanced') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <x-core::textarea
                            :label="trans('core::core.theme_settings.custom_css') . ' (Light Mode)'"
                            name="custom_css"
                            id="custom_css_{{ $scope }}"
                            rows="8"
                            class="font-monospace"
                            value="{{ old('custom_css', $settings->custom_css) }}">
                        </x-core::textarea>
                        <small class="text-muted">Add custom CSS for light mode</small>
                    </div>

                    <div>
                        <x-core::textarea
                            :label="trans('core::core.theme_settings.custom_css') . ' (Dark Mode)'"
                            name="dark_custom_css"
                            id="dark_custom_css_{{ $scope }}"
                            rows="8"
                            class="font-monospace"
                            value="{{ old('dark_custom_css', $settings->dark_custom_css) }}">
                        </x-core::textarea>
                        <small class="text-muted">Add custom CSS for dark mode</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Preview & Actions -->
        <div class="col-lg-4">
            <!-- Live Preview -->
            <div class="card mb-4 sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">{{ trans('core::core.theme_settings.preview') }}</h5>
                </div>
                <div class="card-body">
                    <div class="theme-preview-box" id="preview-{{ $scope }}">
                        <div class="preview-card p-3 mb-3" style="background: white; border-radius: 0.375rem;">
                            <h6>Preview Card</h6>
                            <p class="mb-2">This is how your content will look</p>
                            <button class="btn btn-sm preview-button text-white">Primary Button</button>
                        </div>
                        <p class="small text-muted">Colors and styles will update in real-time</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="ti tabler-device-floppy me-1"></i>{{ trans('core::core.theme_settings.save_changes') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="if(confirm('{{ trans('core::core.theme_settings.reset_confirm') }}')) { document.getElementById('reset-form-{{ $scope }}').submit(); }">
                        <i class="ti tabler-refresh me-1"></i>{{ trans('core::core.theme_settings.reset_defaults') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Reset Form -->
<form id="reset-form-{{ $scope }}" action="{{ route('doctor.theme.settings.reset') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="scope" value="{{ $scope }}">
</form>