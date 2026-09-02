{{--
Swaps the placeholder CSRF token in full-measure statically cached pages for a
real one. Injected inline by default, or served from a dedicated route when
static_caching.script_delivery is "external". Blade data: $csrfPlaceholder
--}}
(function() {
    fetch('/!/csrf', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
    })
    .then((response) => response.json())
    .then((data) => {
        for (const input of document.querySelectorAll('input[value="{{ $csrfPlaceholder }}"]')) {
            input.value = data.csrf;
        }

        for (const meta of document.querySelectorAll('meta[content="{{ $csrfPlaceholder }}"]')) {
            meta.content = data.csrf;
        }

        for (const input of document.querySelectorAll('script[data-csrf="{{ $csrfPlaceholder }}"]')) {
            input.setAttribute('data-csrf', data.csrf);
        }

        if (window.hasOwnProperty('livewire_token')) {
            window.livewire_token = data.csrf
        }

        if (window.livewireScriptConfig) {
            // Replaces token if Livewire is already available. Usually on fast networks.
            window.livewireScriptConfig.csrf = data.csrf;
        } else {
            // Delays replacing the token until Livewire is initialized. Usually on slow networks.
            document.addEventListener('livewire:init', () => window.livewireScriptConfig.csrf = data.csrf);
        }

        document.dispatchEvent(new CustomEvent('statamic:csrf.replaced', { detail: data }));
    });
})();
