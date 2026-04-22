{{--
    Auth :: Password Strength Meter + Requirements Checklist

    Drop-in visual helper that reinforces the server-side strong-password
    policy configured in App\Providers\AppServiceProvider::configurePasswordPolicy()
    (min 8, upper + lower, number, symbol). Safe to use on any auth screen.

    Usage:
        <x-auth::password-strength
            target="password"
            match="password-confirm" {{-- optional: ID of the confirm-password input --}}
{{--  />

Works independently of Vite / front-main.js — JS is inlined and is
guarded against double-binding so the widget survives Livewire /
partial re-renders.
--}}
@props([
    'target' => 'password',
    'match' => null,
])
@php
    $uid = 'pwdmeter_'.uniqid();
@endphp

<div class="password-strength-meter mt-2" data-password-meter="{{ $uid }}"
     data-target="{{ $target }}"
     @if($match) data-match="{{ $match }}" @endif>

    <div class="d-flex align-items-center gap-2 mb-1">
        <small class="text-muted">{{ trans('auth::auth.password_strength_label') }}</small>
        <small class="fw-semibold" data-strength-label>—</small>
    </div>
    <div class="progress" style="height: 6px;">
        <div class="progress-bar" role="progressbar" data-strength-bar
             style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>

    <ul class="list-unstyled small mt-2 mb-0" data-rules>
        <li data-rule="min">
            <i class="ti tabler-circle text-muted me-1"></i>
            <span>{{ trans('auth::auth.password_rule_min') }}</span>
        </li>
        <li data-rule="mixed">
            <i class="ti tabler-circle text-muted me-1"></i>
            <span>{{ trans('auth::auth.password_rule_mixedcase') }}</span>
        </li>
        <li data-rule="number">
            <i class="ti tabler-circle text-muted me-1"></i>
            <span>{{ trans('auth::auth.password_rule_number') }}</span>
        </li>
        <li data-rule="symbol">
            <i class="ti tabler-circle text-muted me-1"></i>
            <span>{{ trans('auth::auth.password_rule_symbol') }}</span>
        </li>
        @if($match)
            <li data-rule="match">
                <i class="ti tabler-circle text-muted me-1"></i>
                <span>{{ trans('auth::auth.password_rule_match') }}</span>
            </li>
        @endif
    </ul>
</div>

<script>
    (function () {
        const root = document.querySelector('[data-password-meter="{{ $uid }}"]');
        if (!root || root.dataset.bound === '1') {
            return;
        }
        root.dataset.bound = '1';

        const targetId = root.getAttribute('data-target');
        const matchId = root.getAttribute('data-match');
        const input = document.getElementById(targetId);
        const matchInput = matchId ? document.getElementById(matchId) : null;

        if (!input) {
            return;
        }

        const bar = root.querySelector('[data-strength-bar]');
        const label = root.querySelector('[data-strength-label]');
        const rulesEl = root.querySelector('[data-rules]');

        const STRENGTHS = [
            {min: 0, pct: 10, cls: 'bg-danger', text: @json(trans('auth::auth.password_strength_very_weak')) },
            {min: 2, pct: 35, cls: 'bg-danger', text: @json(trans('auth::auth.password_strength_weak')) },
            {min: 3, pct: 60, cls: 'bg-warning', text: @json(trans('auth::auth.password_strength_fair')) },
            {min: 4, pct: 85, cls: 'bg-info', text: @json(trans('auth::auth.password_strength_strong')) },
            {min: 5, pct: 100, cls: 'bg-success', text: @json(trans('auth::auth.password_strength_very_strong')) },
        ];

        function evaluate(value) {
            return {
                min: value.length >= 8,
                mixed: /[a-z]/.test(value) && /[A-Z]/.test(value),
                number: /\d/.test(value),
                symbol: /[^A-Za-z0-9]/.test(value),
            };
        }

        function paintRule(el, ok) {
            const icon = el.querySelector('i');
            if (!icon) return;
            icon.classList.remove('tabler-circle', 'tabler-circle-check', 'tabler-circle-x',
                'text-muted', 'text-success', 'text-danger');
            if (ok === true) {
                icon.classList.add('tabler-circle-check', 'text-success');
            } else if (ok === false) {
                icon.classList.add('tabler-circle-x', 'text-danger');
            } else {
                icon.classList.add('tabler-circle', 'text-muted');
            }
        }

        function render() {
            const value = input.value || '';
            const checks = evaluate(value);

            // Per-rule styling
            paintRule(rulesEl.querySelector('[data-rule="min"]'), value.length ? checks.min : null);
            paintRule(rulesEl.querySelector('[data-rule="mixed"]'), value.length ? checks.mixed : null);
            paintRule(rulesEl.querySelector('[data-rule="number"]'), value.length ? checks.number : null);
            paintRule(rulesEl.querySelector('[data-rule="symbol"]'), value.length ? checks.symbol : null);

            if (matchInput) {
                const matchOk = value.length > 0 && value === matchInput.value;
                const matchEl = rulesEl.querySelector('[data-rule="match"]');
                if (matchEl) {
                    paintRule(matchEl, (value.length && matchInput.value.length) ? matchOk : null);
                }
            }

            const score = Object.values(checks).filter(Boolean).length
                + (value.length >= 12 ? 1 : 0); // bonus for length
            const tier = STRENGTHS.slice().reverse().find(s => score >= s.min) || STRENGTHS[0];

            bar.className = 'progress-bar ' + tier.cls;
            bar.style.width = (value.length ? tier.pct : 0) + '%';
            bar.setAttribute('aria-valuenow', value.length ? tier.pct : 0);
            label.textContent = value.length ? tier.text : '—';
        }

        input.addEventListener('input', render);
        if (matchInput) {
            matchInput.addEventListener('input', render);
        }
        render();
    })();
</script>
