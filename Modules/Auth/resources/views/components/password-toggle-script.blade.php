{{--
    Auth :: Password Toggle Script

    Self-contained show / hide password handler used by every patient & doctor
    login / register / reset screen.

    Why this exists:
    The Vuexy theme ships window.Helpers.initPasswordToggle(), but that call is
    only wired up inside main.js (the admin layout). The front layout uses
    front-main.js which does NOT initialise the toggle, so the eye icon on our
    public auth pages was inert.

    This component:
      - Works with or without the `.form-password-toggle` wrapper.
      - Binds once per page (guarded by a data flag).
      - Uses event delegation so it survives Livewire / partial re-renders.
      - Toggles both Tabler icon variants (.tabler-eye / .tabler-eye-off).

    Usage:
        @push('scripts')
            <x-auth::password-toggle-script />
        @endpush
--}}
<script>
    (function () {
        if (window.__authPasswordToggleBound) {
            return;
        }
        window.__authPasswordToggleBound = true;

        const SELECTORS = [
            '.form-password-toggle .input-group-text',
            '.input-group-merge .input-group-text.cursor-pointer',
        ].join(', ');

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest(SELECTORS);
            if (!trigger) {
                return;
            }

            // Must sit next to a password input inside the same input-group.
            const group = trigger.closest('.input-group');
            if (!group) {
                return;
            }

            const input = group.querySelector('input[type="password"], input[data-password-toggle]');
            if (!input) {
                return;
            }

            event.preventDefault();

            const icon = trigger.querySelector('i');
            const isHidden = input.getAttribute('type') === 'password';

            input.setAttribute('type', isHidden ? 'text' : 'password');
            // Remember we already toggled this input so follow-up clicks still work
            // after the browser removes the native `type="password"` attribute.
            input.setAttribute('data-password-toggle', 'true');

            if (icon) {
                if (isHidden) {
                    icon.classList.replace('tabler-eye-off', 'tabler-eye');
                } else {
                    icon.classList.replace('tabler-eye', 'tabler-eye-off');
                }
            }
        });
    })();
</script>
