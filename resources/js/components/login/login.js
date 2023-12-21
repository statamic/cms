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

                    alert('it failed');
                    console.log(response);

                })
                .catch (e => {
                    console.error(e);
                });
        },
    }

};
