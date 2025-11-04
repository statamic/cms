<template>
    <confirmation-modal
        v-if="resetting"
        :title="modalTitle"
        :bodyText="modalBody"
        :buttonText="__('Reset')"
        :danger="true"
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
            if (!url) console.error('FieldsetResetter cannot find reset url');
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
            this.$axios
                .delete(this.resetUrl)
                .then((response) => {
                    this.redirectFromServer = data_get(response, 'data.redirect');
                    this.success();
                })
                .catch(() => {
                    this.$toast.error(__('Something went wrong'));
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
        },

        cancel() {
            this.resetting = false;
        },
    },
};
</script>
