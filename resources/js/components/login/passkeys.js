import { startRegistration, browserSupportsWebAuthn } from '@simplewebauthn/browser';

export default {

    props: {
        webAuthnRoutes: {
            default: {},
            type: Object,
        }
    },

    computed: {
        showWebAuthn() {
            console.log('Supported: ' + browserSupportsWebAuthn());
            return browserSupportsWebAuthn();
        },
    },

    methods: {
        async webAuthn() {
            const authOptionsResponse = await fetch(this.webAuthnRoutes.options);
            const startRegistrationResponse = await startRegistration(await authOptionsResponse.json());

            this.$axios.post(this.webAuthnRoutes.verify, startRegistrationResponse)
                .then(response => {

                    if (response && response.verified) {
                        alert('it worked');
                    } else {
                        alert('it failed');
                        console.log(response);
                    }

                })
                .catch (e => {
                    console.error(e);
                });
        },
    }

};
