import { ref } from 'vue';
import { startAuthentication, browserSupportsWebAuthn, WebAuthnAbortService } from '@simplewebauthn/browser';
import axios from 'axios';

export function usePasskey() {
    const error = ref(null);
    const waiting = ref(false);
    const supported = browserSupportsWebAuthn();

    async function authenticate(optionsUrl, verifyUrl, onSuccess, useBrowserAutofill = false) {
        if (!useBrowserAutofill) waiting.value = true;
        error.value = null;

        try {
            const authOptionsResponse = await fetch(optionsUrl);
            const optionsJSON = await authOptionsResponse.json();

            let startAuthResponse;
            try {
                startAuthResponse = await startAuthentication({ optionsJSON, useBrowserAutofill });
            } catch (e) {
                if (e.name === 'AbortError' || e.name === 'NotAllowedError') return;
                console.error(e);
                error.value = __('Authentication failed.');
                if (!useBrowserAutofill) waiting.value = false;
                return;
            }

            const response = await axios.post(verifyUrl, startAuthResponse);

            if (onSuccess) {
                onSuccess(response.data);
            }
        } catch (e) {
            handleError(e);
        } finally {
            if (!useBrowserAutofill) waiting.value = false;
        }
    }

    function cancel() {
        WebAuthnAbortService.cancelCeremony();
    }

    function handleError(e) {
        if (e.response) {
            const { message } = e.response.data;
            error.value = message;
            return;
        }

        error.value = __('Something went wrong');
    }

    return {
        error,
        waiting,
        supported,
        authenticate,
        cancel,
    };
}
