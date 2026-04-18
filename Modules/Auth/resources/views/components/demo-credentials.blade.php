@props(['userType' => 'patient'])

@if(config('demo.enabled'))
    @php
        $credentials = config("demo.credentials.{$userType}");
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';
        $uniqueId = 'demo-' . $userType . '-' . Str::random(8);

        // Translations for JavaScript (properly escaped)
        $translations = [
            'filling' => trans('auth::auth.filling'),
            'filled_successfully' => trans('auth::auth.filled_successfully'),
            'copied' => trans('auth::auth.copied'),
            'copy_to_clipboard' => trans('auth::auth.copy_to_clipboard'),
        ];
    @endphp

    <div class="demo-credentials-card mb-4"
         id="{{ $uniqueId }}"
         data-translations="{{ json_encode($translations) }}">
        <div class="alert alert-primary mb-0" role="alert">
            <div class="d-flex align-items-start">
                <div class="alert-icon me-3 d-none d-sm-block">
                    <span class="badge badge-center rounded-pill bg-primary">
                        <i class="ti tabler-info-circle fs-5"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-2 d-flex align-items-center">
                        <i class="ti tabler-test-pipe me-2 d-sm-none"></i>
                        {{ trans('auth::auth.demo_mode_title') }}
                    </h6>
                    <p class="mb-3 small text-body">
                        {{ trans('auth::auth.demo_mode_description') }}
                    </p>

                    <div class="demo-credentials-list">
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <div class="credential-row d-flex align-items-center justify-content-between bg-label-secondary rounded p-2">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <i class="ti tabler-mail me-2 text-primary flex-shrink-0"></i>
                                        <small class="text-muted me-2 d-none d-sm-inline">{{ trans('auth::auth.email') }}:</small>
                                        <code class="demo-email text-primary fw-semibold user-select-all text-truncate">{{ $email }}</code>
                                    </div>
                                    <button type="button"
                                            class="btn btn-icon btn-sm btn-text-primary copy-btn flex-shrink-0 ms-2"
                                            data-copy="{{ $email }}"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ trans('auth::auth.copy_to_clipboard') }}">
                                        <i class="ti tabler-copy fs-6"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="credential-row d-flex align-items-center justify-content-between bg-label-secondary rounded p-2">
                                    <div class="d-flex align-items-center overflow-hidden">
                                        <i class="ti tabler-lock me-2 text-primary flex-shrink-0"></i>
                                        <small class="text-muted me-2 d-none d-sm-inline">{{ trans('auth::auth.password') }}:</small>
                                        <code class="demo-password text-primary fw-semibold user-select-all text-truncate">{{ $password }}</code>
                                    </div>
                                    <button type="button"
                                            class="btn btn-icon btn-sm btn-text-primary copy-btn flex-shrink-0 ms-2"
                                            data-copy="{{ $password }}"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="{{ trans('auth::auth.copy_to_clipboard') }}">
                                        <i class="ti tabler-copy fs-6"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button"
                                class="btn btn-primary btn-sm w-100 demo-autofill-btn"
                                data-email="{{ $email }}"
                                data-password="{{ $password }}">
                            <i class="ti tabler-player-play me-2"></i>
                            <span class="btn-text">{{ trans('auth::auth.auto_fill_credentials') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @once
        @push('styles')
            <style>
                .demo-credentials-card .alert {
                    border: 1px solid rgba(var(--bs-primary-rgb), 0.3);
                    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.08) 0%, rgba(var(--bs-primary-rgb), 0.03) 100%);
                }

                .demo-credentials-card .alert-icon .badge {
                    width: 40px;
                    height: 40px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .demo-credentials-card .credential-row {
                    background-color: rgba(var(--bs-secondary-rgb), 0.08) !important;
                    transition: all 0.2s ease;
                }

                .demo-credentials-card .credential-row:hover {
                    background-color: rgba(var(--bs-primary-rgb), 0.12) !important;
                }

                .demo-credentials-card code {
                    background: transparent;
                    padding: 0;
                    font-size: 0.875rem;
                }

                .demo-credentials-card .copy-btn {
                    opacity: 0.6;
                    transition: all 0.2s ease;
                }

                .demo-credentials-card .copy-btn:hover {
                    opacity: 1;
                    transform: scale(1.1);
                }

                .demo-credentials-card .copy-btn.copied {
                    color: var(--bs-success) !important;
                    opacity: 1;
                }

                .demo-credentials-card .copy-btn.copied i::before {
                    content: "\eb7a"; /* tabler-check icon */
                }

                .demo-autofill-btn {
                    transition: all 0.3s ease;
                    position: relative;
                    overflow: hidden;
                }

                .demo-autofill-btn:hover:not(.filling) {
                    transform: translateY(-1px);
                    box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.35);
                }

                .demo-autofill-btn.filling {
                    pointer-events: none;
                    opacity: 0.85;
                }

                .demo-autofill-btn.filling i {
                    animation: demoSpin 0.6s linear infinite;
                }

                .demo-autofill-btn.success {
                    background-color: var(--bs-success) !important;
                    border-color: var(--bs-success) !important;
                }

                @keyframes demoSpin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }

                /* Pulse animation for attention */
                .demo-credentials-card {
                    animation: demoPulse 2s ease-in-out;
                }

                @keyframes demoPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.01); }
                }

                /* Dark mode support */
                [data-bs-theme="dark"] .demo-credentials-card .alert {
                    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.15) 0%, rgba(var(--bs-primary-rgb), 0.06) 100%);
                }

                [data-bs-theme="dark"] .demo-credentials-card .credential-row {
                    background-color: rgba(255, 255, 255, 0.06) !important;
                }

                [data-bs-theme="dark"] .demo-credentials-card .credential-row:hover {
                    background-color: rgba(var(--bs-primary-rgb), 0.18) !important;
                }

                /* RTL Support */
                [dir="rtl"] .demo-credentials-card .alert-icon {
                    margin-left: 1rem;
                    margin-right: 0;
                }

                [dir="rtl"] .demo-credentials-card .copy-btn {
                    margin-right: 0.5rem;
                    margin-left: 0;
                }
            </style>
        @endpush
    @endonce

    @once
        @push('scripts')
            <script>
                (function() {
                    function initDemoCredentials() {
                        document.querySelectorAll('.demo-credentials-card').forEach(function(container) {
                            // Skip if already initialized
                            if (container.dataset.initialized) return;
                            container.dataset.initialized = 'true';

                            const translations = JSON.parse(container.dataset.translations || '{}');
                            const autoFillBtn = container.querySelector('.demo-autofill-btn');
                            const copyBtns = container.querySelectorAll('.copy-btn');

                            // Auto-fill functionality
                            if (autoFillBtn) {
                                autoFillBtn.addEventListener('click', function() {
                                    const btn = this;
                                    const email = btn.dataset.email;
                                    const password = btn.dataset.password;
                                    const btnTextEl = btn.querySelector('.btn-text');
                                    const btnIconEl = btn.querySelector('i');

                                    const emailInput = document.getElementById('email');
                                    const passwordInput = document.getElementById('password');

                                    if (!emailInput || !passwordInput) return;

                                    // Store original content
                                    const originalText = btnTextEl ? btnTextEl.textContent : '';
                                    const originalIconClass = btnIconEl ? btnIconEl.className : '';

                                    // Add filling animation
                                    btn.classList.add('filling');
                                    if (btnIconEl) btnIconEl.className = 'ti tabler-loader me-2';
                                    if (btnTextEl) btnTextEl.textContent = translations.filling || 'Filling...';

                                    // Type into email field
                                    emailInput.value = '';
                                    emailInput.focus();

                                    typeText(emailInput, email, 0, function() {
                                        // Type into password field
                                        passwordInput.value = '';
                                        passwordInput.focus();

                                        typeText(passwordInput, password, 0, function() {
                                            // Show success state
                                            btn.classList.remove('filling');
                                            btn.classList.add('success');
                                            if (btnIconEl) btnIconEl.className = 'ti tabler-check me-2';
                                            if (btnTextEl) btnTextEl.textContent = translations.filled_successfully || 'Filled!';

                                            // Reset after delay
                                            setTimeout(function() {
                                                btn.classList.remove('success');
                                                if (btnIconEl) btnIconEl.className = originalIconClass;
                                                if (btnTextEl) btnTextEl.textContent = originalText;
                                            }, 2000);
                                        });
                                    });
                                });
                            }

                            // Copy to clipboard functionality
                            copyBtns.forEach(function(btn) {
                                btn.addEventListener('click', function() {
                                    const copyBtn = this;
                                    const textToCopy = copyBtn.dataset.copy;

                                    navigator.clipboard.writeText(textToCopy).then(function() {
                                        copyBtn.classList.add('copied');

                                        // Update tooltip if exists
                                        if (typeof bootstrap !== 'undefined') {
                                            const tooltip = bootstrap.Tooltip.getInstance(copyBtn);
                                            if (tooltip) {
                                                tooltip.setContent({ '.tooltip-inner': translations.copied || 'Copied!' });
                                            }
                                        }

                                        setTimeout(function() {
                                            copyBtn.classList.remove('copied');
                                            if (typeof bootstrap !== 'undefined') {
                                                const tooltip = bootstrap.Tooltip.getInstance(copyBtn);
                                                if (tooltip) {
                                                    tooltip.setContent({ '.tooltip-inner': translations.copy_to_clipboard || 'Copy' });
                                                }
                                            }
                                        }, 1500);
                                    }).catch(function(err) {
                                        console.error('Failed to copy:', err);
                                    });
                                });
                            });

                            // Initialize tooltips
                            if (typeof bootstrap !== 'undefined') {
                                container.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                                    new bootstrap.Tooltip(el);
                                });
                            }
                        });
                    }

                    // Typing effect function
                    function typeText(element, text, index, callback) {
                        if (index < text.length) {
                            element.value += text.charAt(index);
                            // Trigger events for form validation
                            element.dispatchEvent(new Event('input', { bubbles: true }));
                            element.dispatchEvent(new Event('change', { bubbles: true }));

                            setTimeout(function() {
                                typeText(element, text, index + 1, callback);
                            }, 25);
                        } else if (callback) {
                            callback();
                        }
                    }

                    // Initialize on DOM ready
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initDemoCredentials);
                    } else {
                        initDemoCredentials();
                    }
                })();
            </script>
        @endpush
    @endonce
@endif
