<template>
    <confirmation-modal
        v-if="deleting"
        :title="modalTitle"
        :bodyText="modalBody"
        :buttonText="__('Delete')"
        :danger="true"
        @confirm="confirmed"
        @cancel="cancel"
    >
    </confirmation-modal>
</template>

<script>
export default {

    props: {
        resource: {
            type: Object
        },
        resourceTitle: {
            type: String
        },
        route: {
            type: String,
        },
        redirect: {
            type: String
        },
        reload: {
            type: Boolean
        }
    },

    data() {
        return {
            deleting: false,
            redirectFromServer: null,
        }
    },

    computed: {
        title() {
            return data_get(this.resource, 'title', this.resourceTitle);
        },

        modalTitle() {
            return __('Delete :resource', {resource: this.title});
        },

        modalBody() {
            return __('Are you sure you want to delete this item?');
        },

        deleteUrl() {
            let url = data_get(this.resource, 'delete_url', this.route);
            if (! url) console.error('ResourceDeleter cannot find delete url');
            return url;
        },

        redirectUrl() {
            return this.redirect || this.redirectFromServer;
        },
    },

    methods: {
        confirm() {
            this.deleting = true;
        },

        confirmed() {
            this.$axios.delete(this.deleteUrl)
                .then(response => {
                    this.redirectFromServer = data_get(response, 'data.redirect');
                    this.success();
                })
                .catch(() => {
                    this.$toast.error(__('Something went wrong'));
                });
        },

        success() {
            if (this.redirectUrl) {
                location.href = this.redirectUrl;
                return;
            }

            if (this.reload) {
                location.reload();
                return;
            }

            this.$toast.success(__('Deleted'));
            this.$emit('deleted');
        },

        cancel() {
            this.deleting = false;
        }
    }
}
</script>
