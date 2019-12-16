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
        resourceTitle: {
            type: String,
            required: true
        },
        route: {
            type: String,
            required: true
        },
        redirect: {
            type: String
        }
    },

    data() {
        return {
            deleting: false,
        }
    },

    computed: {
        modalTitle() {
            return __('Delete') + ' ' + this.resourceTitle;
        },

        modalBody() {
            return __('Are you sure you want to delete this item?');
        }
    },

    methods: {
        confirm() {
            this.deleting = true;
        },

        confirmed() {
            this.$axios.delete(this.route)
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

            location.reload();
        },

        cancel() {
            this.deleting = false;
        }
    }
}
</script>
