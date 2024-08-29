import { startAuthentication, browserSupportsWebAuthn } from '@simplewebauthn/browser';

export default {

    props: {
        showEmailLogin: {
            default: false
        },
        hasError: {
            default: false
        },
        webAuthnRoutes: {
            default: {},
            type: Object,
        }
    },

    data() {
        return {
            busy: false
        }
    },

    mounted() {
        if (this.hasError) {
            this.$el.parentElement.parentElement.classList.add('animation-shake');
        }
    },

    computed: {
        showWebAuthn() {
            return browserSupportsWebAuthn();
        },

        showWebAuthnError() {
            return this.webAuthnError !== false;
        },
    },

    data() {
        return {
            webAuthnError: false,
        }
    },

    methods: {
        async webAuthn() {
            const authOptionsResponse = await fetch(this.webAuthnRoutes.options);
            const startAuthResponse = await startAuthentication(await authOptionsResponse.json());

            this.$axios.post(this.webAuthnRoutes.verify, startAuthResponse)
                .then(response => {
                    if (response && response.data.redirect) {
                        location.href = response.data.redirect;
                        return;
                    }

                    this.webAuthnError = response.data.message;
                }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            if (e.response) {
                const { message, errors } = e.response.data;
                this.webAuthnError = message;
                return;
            }

            this.webAuthnError = __('Something went wrong');
        },
    }

};
