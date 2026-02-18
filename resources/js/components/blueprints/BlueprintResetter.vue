<template>
    <confirmation-modal
        :open="resetting"
        :title="modalTitle"
        :bodyText="modalBody"
        :buttonText="__('Reset')"
        :danger="true"
        :busy="submitting"
        @confirm="confirmed"
        @cancel="cancel"
    >
    </confirmation-modal>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
    props: {
        resource: {
            type: Object,
        },
        resourceTitle: {
            type: String,
        },
        route: {
            type: String,
        },
        redirect: {
            type: String,
        },
        reload: {
            type: Boolean,
        },
    },

    data() {
        return {
            resetting: false,
            redirectFromServer: null,
            submitting: false,
        };
    },

    computed: {
        title() {
            return data_get(this.resource, 'title', this.resourceTitle);
        },

        modalTitle() {
            return __('Reset :resource', { resource: this.title });
        },

        modalBody() {
            return __('Are you sure you want to reset this item?');
        },

        resetUrl() {
            let url = data_get(this.resource, 'reset_url', this.route);
            if (!url) console.error('BlueprintResetter cannot find reset url');
            return url;
        },

        redirectUrl() {
            return this.redirect || this.redirectFromServer;
        },
    },

    methods: {
        confirm() {
            this.resetting = true;
        },

        confirmed() {
            this.submitting = true;

            this.$axios
                .delete(this.resetUrl)
                .then((response) => {
                    this.redirectFromServer = data_get(response, 'data.redirect');
                    this.success();
                })
                .catch(() => {
                    this.$toast.error(__('Something went wrong'));
                    this.submitting = false;
                });
        },

        success() {
            if (this.redirectUrl) {
                router.get(this.redirectUrl);
                return;
            }

            if (this.reload) {
                router.reload();
                return;
            }

            this.$toast.success(__('Reset'));
            this.$emit('reset');
            this.submitting = false;
        },

        cancel() {
            this.resetting = false;
        },
    },
};
</script>
