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
            emailChecked: false,
            passwordEnabled: true,
            webAuthnEnabled: false,
            webAuthnError: false,
        }
    },

    methods: {
        checkUser: _.debounce(function (event) {
            this.$axios.post(this.webAuthnRoutes.checkuser, {
                email: event.target.value
            })
                .then(response => {
                    this.emailChecked = true;

                    if (response.data && response.data.includes('passkey')) {
                        this.passwordEnabled = response.data.includes('password');
                        this.webAuthnEnabled = true;
                        this.webAuthnError = browserSupportsWebAuthn() ? '' : __('Your browser doesnt support passkeys');

                        return;
                    }

                    this.passwordEnabled = true;
                    this.webAuthnEnabled = false;
                    this.webAuthnError = '';
                }).catch(e => this.handleAxiosError(e));
        }, 300),

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
