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
            return __('Are you sure you want to delete this') + ' ' + this.resourceTitle.toLowerCase();
        }
    },

    methods: {
        confirm() {
            this.deleting = true;
        },

        confirmed() {
            this.$axios.delete(this.route)
                .then(() => {
                    location.reload();
                })
                .catch(() => {
                    this.$notify.error(__('Something went wrong'));
                });
        },

        cancel() {
            this.deleting = false;
        }
    }
}
</script>
