<template>

    <div>
        <button class="btn" @click="confirming = true">
            Restore
        </button>

        <confirmation-modal
            v-if="confirming"
            :title="__('Restore Revision')"
            :buttonText="__('Restore')"
            @confirm="restore"
            @cancel="confirming = false"
        >
            <p class="mb-2">Are you sure you want to restore this revision?</p>
            <p class="mb-3">Your working copy will be replaced by the contents of this revision.</p>
        </confirmation-modal>
    </div>

</template>

<script>
export default {

    props: {
        revision: Object,
        url: String,
        reference: String,
    },

    data() {
        return {
            confirming: false,
        }
    },

    methods: {

        restore() {
            const payload = {
                revision: this.revision.date,
            };

            this.$axios.post(this.url, payload).then(response => {
                Statamic.$hooks
                    .run('revision.restored', { reference: this.reference })
                    .then(() => {
                        this.$dirty.disableWarning();
                        window.location.reload();
                    });
            })
        }

    }

}
</script>
