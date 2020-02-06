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
        resourceType: {
            type: String,
            required: true
        },
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
        }
    },

    computed: {
        title() {
            return data_get(this.resource, 'title', this.resourceTitle);
        },

        modalTitle() {
            return [__('Delete'), this.title, this.resourceType]
                .filter(x => x)
                .join(' ');
        },

        modalBody() {
            return __('Are you sure you want to delete this item?');
        },

        deleteUrl() {
            let url = data_get(this.resource, 'delete_url', this.route);
            if (! url) console.error('ResourceDeleter cannot find delete url');
            return url;
        },

        successMessage() {
            return [this.resourceType, __('deleted')]
                .filter(x => x)
                .join(' ');
        },
    },

    methods: {
        confirm() {
            this.deleting = true;
        },

        confirmed() {
            this.$axios.delete(this.deleteUrl)
                .then(() => {
                    this.success();
                })
                .catch(() => {
                    this.$toast.error(__('Something went wrong'));
                });
        },

        success() {
            if (this.redirect) {
                location.href = this.redirect;
                return;
            }

            if (this.reload) {
                location.reload();
                return;
            }

            this.$toast.success(this.successMessage);
            this.$emit('deleted');
        },

        cancel() {
            this.deleting = false;
        }
    }
}
</script>
