import { startAuthentication, startRegistration, browserSupportsWebAuthn, WebAuthnAbortService } from '@simplewebauthn/browser';

export default class Passkeys {
    constructor() {
        this.supported = browserSupportsWebAuthn();
        this._waiting = false;
        this._error = null;
        this._defaults = {};
    }

    configure(defaults) {
        this._defaults = defaults;
        return this;
    }

    get waiting() {
        return this._waiting;
    }

    get error() {
        return this._error;
    }

    /**
     * Authenticate with a passkey.
     *
     * @param {Object} options
     * @param {string} options.optionsUrl - URL to fetch assertion options
     * @param {string} options.verifyUrl - URL to verify the assertion
     * @param {Function} [options.onSuccess] - Callback on success with response data
     * @param {Function} [options.onError] - Callback on error with error object
     * @param {boolean} [options.useBrowserAutofill=false] - Use browser autofill UI
     * @param {string} [options.csrfToken] - Override CSRF token
     */
    async authenticate(options = {}) {
        const {
            optionsUrl,
            verifyUrl,
            onSuccess,
            onError,
            useBrowserAutofill = false,
            csrfToken,
        } = { ...this._defaults, ...options };

        if (!useBrowserAutofill) {
            this._waiting = true;
        }
        this._error = null;

        try {
            const authOptionsResponse = await fetch(optionsUrl, {
                credentials: 'same-origin',
            });

            if (!authOptionsResponse.ok) {
                throw new Error('Failed to fetch authentication options');
            }

            const optionsJSON = await authOptionsResponse.json();

            let authResponse;
            try {
                authResponse = await startAuthentication({ optionsJSON, useBrowserAutofill });
            } catch (e) {
                if (e.name === 'AbortError' || e.name === 'NotAllowedError') {
                    return;
                }
                console.error(e);
                this._error = 'Authentication failed.';
                if (onError) {
                    onError({ message: this._error, originalError: e });
                }
                return;
            }

            const verifyResponse = await fetch(verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || this._getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(authResponse),
            });

            const data = await verifyResponse.json();

            if (!verifyResponse.ok) {
                this._error = data.message || 'Verification failed.';
                if (onError) {
                    onError({ message: this._error, status: verifyResponse.status });
                }
                return;
            }

            if (onSuccess) {
                onSuccess(data);
            }
        } catch (e) {
            this._handleError(e, onError);
        } finally {
            if (!useBrowserAutofill) {
                this._waiting = false;
            }
        }
    }

    /**
     * Register a new passkey.
     *
     * @param {Object} options
     * @param {string} options.optionsUrl - URL to fetch attestation options
     * @param {string} options.verifyUrl - URL to verify and store the passkey
     * @param {string} [options.name='Passkey'] - Name for the passkey
     * @param {Function} [options.onSuccess] - Callback on success with response data
     * @param {Function} [options.onError] - Callback on error with error object
     * @param {string} [options.csrfToken] - Override CSRF token
     */
    async register(options = {}) {
        const {
            optionsUrl,
            verifyUrl,
            name = 'Passkey',
            onSuccess,
            onError,
            csrfToken,
        } = { ...this._defaults, ...options };

        this._waiting = true;
        this._error = null;

        try {
            const createOptionsResponse = await fetch(optionsUrl, {
                credentials: 'same-origin',
            });

            if (!createOptionsResponse.ok) {
                throw new Error('Failed to fetch registration options');
            }

            const optionsJSON = await createOptionsResponse.json();

            let registrationResponse;
            try {
                registrationResponse = await startRegistration({ optionsJSON });
            } catch (e) {
                if (e.name === 'AbortError' || e.name === 'NotAllowedError') {
                    return;
                }
                console.error(e);
                this._error = 'Registration failed.';
                if (onError) {
                    onError({ message: this._error, originalError: e });
                }
                return;
            }

            const verifyResponse = await fetch(verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || this._getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    ...registrationResponse,
                    name,
                }),
            });

            const data = await verifyResponse.json();

            if (!verifyResponse.ok) {
                this._error = data.message || 'Verification failed.';
                if (onError) {
                    onError({ message: this._error, status: verifyResponse.status });
                }
                return;
            }

            if (onSuccess) {
                onSuccess(data);
            }
        } catch (e) {
            this._handleError(e, onError);
        } finally {
            this._waiting = false;
        }
    }

    /**
     * Cancel any ongoing WebAuthn ceremony.
     */
    cancel() {
        WebAuthnAbortService.cancelCeremony();
    }

    /**
     * Initialize browser autofill for passkey authentication.
     * Call this on page load to enable passkey suggestions in form fields.
     *
     * @param {Object} options
     * @param {string} options.optionsUrl - URL to fetch assertion options
     * @param {string} options.verifyUrl - URL to verify the assertion
     * @param {Function} [options.onSuccess] - Callback on success with response data
     * @param {Function} [options.onError] - Callback on error with error object
     * @param {string} [options.csrfToken] - Override CSRF token
     */
    initAutofill(options = {}) {
        if (!this.supported) {
            return;
        }

        this.authenticate({
            ...options,
            useBrowserAutofill: true,
        });
    }

    /**
     * Get the CSRF token from the page.
     * @private
     */
    _getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }

        const input = document.querySelector('input[name="_token"]');
        if (input) {
            return input.value;
        }

        return '';
    }

    /**
     * Handle errors consistently.
     * @private
     */
    _handleError(e, onError) {
        this._error = e.message || 'Something went wrong';

        if (onError) {
            onError({ message: this._error, originalError: e });
        }
    }
}
