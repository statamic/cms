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

            const verificationResponse = await fetch(this.webAuthnRoutes.verify, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(startAuthResponse),
            });

            const verificationJSON = await verificationResp.json();

            if (verificationJSON && verificationJSON.verified) {
                alert('it worked');
            } else {
                alert('it failed');
                console.log(verificationJSON);
            }
        },
    }

};
