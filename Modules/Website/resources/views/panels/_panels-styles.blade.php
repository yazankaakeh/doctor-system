{{--
    Shared Panel Styles - Clean, Modern, Minimal Design System
    Uses Bootstrap theme variables from theme_settings table
    Supports RTL, Dark Mode, Responsive
--}}
<style>
    /* ========================================
       CSS VARIABLES - Using Theme Settings
       ======================================== */
    :root {
        /* Theme Colors from theme_settings */
        --panel-primary: var(--bs-primary, #4f46e5);
        --panel-primary-rgb: var(--bs-primary-rgb, 79, 70, 229);
        --panel-secondary: var(--bs-secondary, #1e293b);
        --panel-secondary-rgb: var(--bs-secondary-rgb, 30, 41, 59);
        --panel-success: var(--bs-success, #10b981);
        --panel-warning: var(--bs-warning, #f59e0b);
        --panel-danger: var(--bs-danger, #ef4444);
        --panel-info: var(--bs-info, #06b6d4);

        /* Neutral Colors - Simple and Clean */
        --panel-text: #1e293b;
        --panel-text-muted: #64748b;
        --panel-text-light: #94a3b8;
        --panel-border: #e2e8f0;
        --panel-bg: #ffffff;
        --panel-bg-subtle: #f8fafc;
        --panel-bg-muted: #f1f5f9;

        /* Design Tokens */
        --panel-radius-sm: 8px;
        --panel-radius: 12px;
        --panel-radius-lg: 16px;
        --panel-radius-xl: 24px;
        --panel-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        --panel-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        --panel-shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.08);
        --panel-transition: all 0.3s ease;
        --panel-transition-slow: all 0.5s ease;
    }

    /* Dark Mode */
    [data-bs-theme="dark"] {
        --panel-text: #f1f5f9;
        --panel-text-muted: #94a3b8;
        --panel-text-light: #64748b;
        --panel-border: #334155;
        --panel-bg: #0f172a;
        --panel-bg-subtle: #1e293b;
        --panel-bg-muted: #1e293b;
    }

    /* ========================================
       BASE SECTION
       ======================================== */
    .panel-section {
        position: relative;
        padding: 100px 0;
        overflow: hidden;
        background: var(--panel-bg);
    }

    .panel-section-sm {
        padding: 60px 0;
    }

    .panel-section-lg {
        padding: 120px 0;
    }

    @media (max-width: 768px) {
        .panel-section {
            padding: 60px 0;
        }
        .panel-section-lg {
            padding: 80px 0;
        }
    }

    /* ========================================
       SECTION HEADER - Clean & Minimal
       ======================================== */
    .panel-header {
        text-align: center;
        max-width: 680px;
        margin: 0 auto 60px;
    }

    .panel-header-left {
        text-align: start;
        margin-left: 0;
        margin-right: 0;
    }

    .panel-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        font-size: 0.8125rem;
        font-weight: 600;
        letter-spacing: 0.025em;
        text-transform: uppercase;
        border-radius: 50px;
        background: rgba(var(--panel-primary-rgb), 0.08);
        color: var(--panel-primary);
        margin-bottom: 20px;
    }

    .panel-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        background: currentColor;
        border-radius: 50%;
    }

    .panel-title {
        font-size: clamp(1.75rem, 4vw, 2.75rem);
        font-weight: 700;
        color: var(--panel-text);
        line-height: 1.25;
        margin-bottom: 16px;
        letter-spacing: -0.025em;
    }

    .panel-title-white {
        color: #fff !important;
    }

    .panel-description {
        font-size: 1.0625rem;
        color: var(--panel-text-muted);
        line-height: 1.7;
        margin: 0;
    }

    .panel-description-white {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    /* ========================================
       CARDS - Clean with Subtle Shadows
       ======================================== */
    .panel-card {
        background: var(--panel-bg);
        border: 1px solid var(--panel-border);
        border-radius: var(--panel-radius-lg);
        transition: var(--panel-transition);
        height: 100%;
        overflow: hidden;
    }

    .panel-card:hover {
        border-color: transparent;
        box-shadow: var(--panel-shadow-lg);
        transform: translateY(-4px);
    }

    .panel-card-body {
        padding: 32px;
    }

    @media (max-width: 576px) {
        .panel-card-body {
            padding: 24px;
        }
    }

    /* ========================================
       ICON BOX - Simple & Clean
       ======================================== */
    .panel-icon-box {
        width: 56px;
        height: 56px;
        border-radius: var(--panel-radius);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--panel-primary-rgb), 0.08);
        color: var(--panel-primary);
        font-size: 1.5rem;
        margin-bottom: 20px;
        transition: var(--panel-transition);
    }

    .panel-card:hover .panel-icon-box {
        background: var(--panel-primary);
        color: #fff;
    }

    .panel-icon-box-sm {
        width: 44px;
        height: 44px;
        font-size: 1.25rem;
    }

    .panel-icon-box-lg {
        width: 72px;
        height: 72px;
        font-size: 1.75rem;
    }

    /* ========================================
       BUTTONS - Modern & Clean
       ======================================== */
    .panel-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 28px;
        font-size: 0.9375rem;
        font-weight: 600;
        border-radius: 50px;
        border: 2px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: var(--panel-transition);
    }

    .panel-btn-sm {
        padding: 8px 20px;
        font-size: 0.875rem;
    }

    .panel-btn-lg {
        padding: 16px 36px;
        font-size: 1rem;
    }

    .panel-btn-primary {
        background: var(--panel-primary);
        color: #fff;
    }

    .panel-btn-primary:hover {
        background: color-mix(in srgb, var(--panel-primary) 90%, #000);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(var(--panel-primary-rgb), 0.3);
    }

    .panel-btn-secondary {
        background: var(--panel-secondary);
        color: #fff;
    }

    .panel-btn-secondary:hover {
        background: color-mix(in srgb, var(--panel-secondary) 90%, #000);
        color: #fff;
        transform: translateY(-2px);
    }

    .panel-btn-outline {
        background: transparent;
        border-color: var(--panel-border);
        color: var(--panel-text);
    }

    .panel-btn-outline:hover {
        background: var(--panel-bg-subtle);
        border-color: var(--panel-text-light);
        color: var(--panel-text);
    }

    .panel-btn-outline-primary {
        border-color: var(--panel-primary);
        color: var(--panel-primary);
    }

    .panel-btn-outline-primary:hover {
        background: var(--panel-primary);
        color: #fff;
    }

    .panel-btn-white {
        background: #fff;
        color: var(--panel-text);
    }

    .panel-btn-white:hover {
        background: var(--panel-bg-subtle);
        color: var(--panel-text);
        transform: translateY(-2px);
    }

    .panel-btn-ghost {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .panel-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    /* Fallback for browsers without color-mix */
    @supports not (background: color-mix(in srgb, red, blue)) {
        .panel-btn-primary:hover {
            filter: brightness(0.95);
        }
        .panel-btn-secondary:hover {
            filter: brightness(0.95);
        }
    }

    /* ========================================
       FORM ELEMENTS
       ======================================== */
    .panel-form .form-control,
    .panel-form .form-select {
        padding: 14px 18px;
        font-size: 0.9375rem;
        border: 1px solid var(--panel-border);
        border-radius: var(--panel-radius);
        background: var(--panel-bg);
        color: var(--panel-text);
        transition: var(--panel-transition);
    }

    .panel-form .form-control:focus,
    .panel-form .form-select:focus {
        border-color: var(--panel-primary);
        box-shadow: 0 0 0 3px rgba(var(--panel-primary-rgb), 0.1);
        outline: none;
    }

    .panel-form .form-control::placeholder {
        color: var(--panel-text-light);
    }

    .panel-form .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--panel-text);
        margin-bottom: 8px;
    }

    /* ========================================
       CONTACT INFO ITEMS
       ======================================== */
    .panel-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 20px 0;
        border-bottom: 1px solid var(--panel-border);
    }

    .panel-contact-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .panel-contact-item:first-child {
        padding-top: 0;
    }

    .panel-contact-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--panel-radius);
        background: rgba(var(--panel-primary-rgb), 0.08);
        color: var(--panel-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: var(--panel-transition);
    }

    .panel-contact-item:hover .panel-contact-icon {
        background: var(--panel-primary);
        color: #fff;
    }

    .panel-contact-content h6 {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--panel-text-light);
        margin-bottom: 4px;
    }

    .panel-contact-content p,
    .panel-contact-content a {
        font-size: 1rem;
        color: var(--panel-text);
        margin: 0;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .panel-contact-content a:hover {
        color: var(--panel-primary);
    }

    /* ========================================
       SOCIAL LINKS
       ======================================== */
    .panel-social-links {
        display: flex;
        gap: 8px;
    }

    .panel-social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--panel-bg-muted);
        color: var(--panel-text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: var(--panel-transition);
        font-size: 1.125rem;
    }

    .panel-social-link:hover {
        background: var(--panel-primary);
        color: #fff;
        transform: translateY(-2px);
    }

    /* ========================================
       AVATAR
       ======================================== */
    .panel-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--panel-bg);
        box-shadow: var(--panel-shadow-sm);
    }

    .panel-avatar-lg {
        width: 100px;
        height: 100px;
        border-width: 3px;
    }

    .panel-avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--panel-primary);
        color: #fff;
        font-weight: 700;
        font-size: 1.25rem;
    }

    @supports (background: color-mix(in srgb, red, blue)) {
        .panel-avatar-placeholder {
            background: linear-gradient(135deg, var(--panel-primary), color-mix(in srgb, var(--panel-primary) 70%, var(--panel-secondary)));
        }
    }

    /* ========================================
       STARS RATING
       ======================================== */
    .panel-stars {
        display: flex;
        gap: 2px;
        color: #fbbf24;
    }

    .panel-stars i {
        font-size: 1rem;
    }

    .panel-stars-muted i {
        color: var(--panel-text-light);
    }

    /* ========================================
       ACCORDION
       ======================================== */
    .panel-accordion .accordion-item {
        border: 1px solid var(--panel-border);
        background: var(--panel-bg);
        border-radius: var(--panel-radius-lg) !important;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .panel-accordion .accordion-button {
        padding: 20px 24px;
        font-weight: 600;
        font-size: 1rem;
        color: var(--panel-text);
        background: var(--panel-bg);
        box-shadow: none !important;
    }

    .panel-accordion .accordion-button:not(.collapsed) {
        color: var(--panel-primary);
        background: var(--panel-bg);
    }

    .panel-accordion .accordion-button::after {
        width: 1.125rem;
        height: 1.125rem;
        background-size: 1.125rem;
    }

    /* Dark mode accordion icon */
    [data-bs-theme="dark"] .panel-accordion .accordion-button::after {
        filter: invert(1) brightness(0.8);
    }

    [data-bs-theme="dark"] .panel-accordion .accordion-button:not(.collapsed)::after {
        filter: invert(1) brightness(0.8);
    }

    .panel-accordion .accordion-body {
        padding: 0 24px 24px;
        color: var(--panel-text-muted);
        line-height: 1.7;
    }

    /* ========================================
       TEAM CARD
       ======================================== */
    .panel-team-card {
        text-align: center;
        background: var(--panel-bg);
        border: 1px solid var(--panel-border);
        border-radius: var(--panel-radius-xl);
        padding: 40px 28px;
        transition: var(--panel-transition);
    }

    .panel-team-card:hover {
        border-color: transparent;
        box-shadow: var(--panel-shadow-lg);
        transform: translateY(-4px);
    }

    .panel-team-card .panel-avatar-lg {
        margin-bottom: 20px;
    }

    .panel-team-name {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--panel-text);
        margin-bottom: 4px;
    }

    .panel-team-role {
        font-size: 0.875rem;
        color: var(--panel-primary);
        font-weight: 500;
        margin-bottom: 12px;
    }

    .panel-team-bio {
        font-size: 0.9375rem;
        color: var(--panel-text-muted);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .panel-team-profile-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--panel-primary);
        background: rgba(var(--panel-primary-rgb), 0.08);
        border-radius: 50px;
        text-decoration: none;
        transition: var(--panel-transition);
        margin-top: 8px;
    }

    .panel-team-profile-link:hover {
        background: var(--panel-primary);
        color: #fff;
        transform: translateY(-2px);
    }

    .panel-team-profile-link i {
        font-size: 1rem;
    }

    /* ========================================
       REVIEW CARD
       ======================================== */
    .panel-review-card {
        background: var(--panel-bg);
        border: 1px solid var(--panel-border);
        border-radius: var(--panel-radius-xl);
        padding: 32px;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: var(--panel-transition);
    }

    .panel-review-card:hover {
        border-color: transparent;
        box-shadow: var(--panel-shadow-lg);
    }

    .panel-review-content {
        font-size: 1rem;
        color: var(--panel-text-muted);
        line-height: 1.8;
        margin-bottom: 24px;
        flex-grow: 1;
    }

    .panel-review-author {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-top: 20px;
        border-top: 1px solid var(--panel-border);
        margin-top: auto;
    }

    .panel-review-author-info h6 {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--panel-text);
        margin-bottom: 2px;
    }

    .panel-review-author-info span {
        font-size: 0.8125rem;
        color: var(--panel-text-light);
    }

    /* ========================================
       STATS
       ======================================== */
    .panel-stat {
        text-align: center;
        padding: 24px;
    }

    .panel-stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
        margin-bottom: 16px;
    }

    .panel-stat-number {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        color: #fff;
        line-height: 1;
        margin-bottom: 8px;
    }

    .panel-stat-label {
        font-size: 0.9375rem;
        color: rgba(255, 255, 255, 0.7);
    }

    /* ========================================
       GALLERY
       ======================================== */
    .panel-gallery-item {
        position: relative;
        border-radius: var(--panel-radius-lg);
        overflow: hidden;
        aspect-ratio: 1;
        cursor: pointer;
    }

    .panel-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--panel-transition-slow);
    }

    .panel-gallery-item:hover img {
        transform: scale(1.08);
    }

    .panel-gallery-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, transparent 60%);
        opacity: 0;
        transition: var(--panel-transition);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 20px;
        color: #fff;
    }

    .panel-gallery-item:hover .panel-gallery-overlay {
        opacity: 1;
    }

    .panel-gallery-zoom {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        width: 48px;
        height: 48px;
        background: var(--panel-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        opacity: 0;
        transition: var(--panel-transition);
    }

    .panel-gallery-item:hover .panel-gallery-zoom {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    /* ========================================
       BACKGROUNDS
       ======================================== */
    .panel-bg-white {
        background: var(--panel-bg);
    }

    .panel-bg-subtle {
        background: var(--panel-bg-subtle);
    }

    .panel-bg-muted {
        background: var(--panel-bg-muted);
    }

    .panel-bg-dark {
        background: var(--panel-secondary);
    }

    .panel-bg-gradient {
        background: linear-gradient(135deg, var(--panel-secondary) 0%, color-mix(in srgb, var(--panel-secondary) 60%, var(--panel-primary)) 100%);
    }

    @supports not (background: color-mix(in srgb, red, blue)) {
        .panel-bg-gradient {
            background: linear-gradient(135deg, var(--panel-secondary) 0%, var(--panel-primary) 100%);
        }
    }

    /* ========================================
       DECORATIVE ELEMENTS - Subtle
       ======================================== */
    .panel-decoration {
        position: absolute;
        pointer-events: none;
    }

    .panel-dot-pattern {
        background-image: radial-gradient(circle, var(--panel-border) 1px, transparent 1px);
        background-size: 24px 24px;
        opacity: 0.5;
    }

    /* ========================================
       ANIMATIONS - Subtle
       ======================================== */
    .panel-animate {
        opacity: 0;
        transform: translateY(20px);
        animation: panelFadeUp 0.6s ease forwards;
    }

    .panel-animate:nth-child(1) { animation-delay: 0.05s; }
    .panel-animate:nth-child(2) { animation-delay: 0.1s; }
    .panel-animate:nth-child(3) { animation-delay: 0.15s; }
    .panel-animate:nth-child(4) { animation-delay: 0.2s; }
    .panel-animate:nth-child(5) { animation-delay: 0.25s; }
    .panel-animate:nth-child(6) { animation-delay: 0.3s; }

    @keyframes panelFadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ========================================
       RTL SUPPORT
       ======================================== */
    [dir="rtl"] .panel-contact-item {
        flex-direction: row;
    }

    [dir="rtl"] .panel-review-author {
        flex-direction: row;
    }

    /* ========================================
       DIVIDER
       ======================================== */
    .panel-divider {
        height: 1px;
        background: var(--panel-border);
        margin: 32px 0;
    }

    /* ========================================
       RESPONSIVE
       ======================================== */
    @media (max-width: 768px) {
        .panel-header {
            margin-bottom: 40px;
        }

        .panel-card-body {
            padding: 24px;
        }

        .panel-team-card {
            padding: 28px 20px;
        }

        .panel-review-card {
            padding: 24px;
        }
    }

    /* ========================================
       PRINT STYLES
       ======================================== */
    @media print {
        .panel-section {
            padding: 40px 0;
        }

        .panel-card,
        .panel-team-card,
        .panel-review-card {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
    }
</style>
