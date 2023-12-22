import { startRegistration, browserSupportsWebAuthn } from '@simplewebauthn/browser';

export default {

    props: {
        webAuthnRoutes: {
            default: {},
            type: Object,
        }
    },

    computed: {
        showErrorModal() {
            return this.error !== false;
        },

        showWebAuthn() {
            return browserSupportsWebAuthn();
        },
    },

    data() {
        return {
            error: false,
        }
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
                    if (response && response.data.verified) {
                        location.reload();
                        return;
                    }

                    this.error = response.data.message;
                }).catch(e => this.handleAxiosError(e));

        },

        handleAxiosError(e) {
            if (e.response) {
                const { message, errors } = e.response.data;
                this.error = message;
                return;
            }

            this.error = __('Something went wrong');
        },
    }

};
