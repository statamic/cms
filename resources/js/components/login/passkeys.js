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
            return browserSupportsWebAuthn();
        },
    },

    methods: {
        deletePasskey(id, target) {
            if (confirm(__('Are you sure?'))) {
                this.$axios.delete(this.webAuthnRoutes.delete + id).then(response => {
                    let row = target.closest('tr');
                    row.parentNode.removeChild(row);
                });
            }
        },

        async webAuthn() {
            const authOptionsResponse = await fetch(this.webAuthnRoutes.options);
            const startRegistrationResponse = await startRegistration(await authOptionsResponse.json());

            this.$axios.post(this.webAuthnRoutes.verify, startRegistrationResponse)
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
