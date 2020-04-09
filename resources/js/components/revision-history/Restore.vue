<template>

    <div>
        <button class="btn" @click="confirming = true" v-text="__('Restore')" />

        <confirmation-modal
            v-if="confirming"
            :title="__('Restore Revision')"
            :buttonText="__('Restore')"
            @confirm="restore"
            @cancel="confirming = false"
        >
            <p class="mb-2" v-text="__('Are you sure you want to restore this revision?')" />
            <p class="mb-3" v-text="__('Your working copy will be replaced by the contents of this revision.')" />
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
