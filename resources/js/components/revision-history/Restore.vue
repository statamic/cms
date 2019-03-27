<template>

    <div>
        <button class="btn btn-flat" @click="confirming = true">
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
            <p class="mb-3">A new revision will be created based on the state of the revision you've selected.</p>
            <text-input v-model="revisionMessage" :placeholder="__('Notes about this revision')" />
        </confirmation-modal>
    </div>

</template>

<script>
export default {

    props: {
        revision: Object,
        url: String
    },

    data() {
        return {
            confirming: false,
            revisionMessage: null
        }
    },

    methods: {

        restore() {
            const payload = {
                revision: this.revision.date,
                message: this.revisionMessage,
            };

            this.$axios.post(this.url, payload).then(response => {
                window.location.reload();
            })
        }

    }

}
</script>
