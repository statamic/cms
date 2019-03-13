<template>

    <modal name="folder-editor" :pivotY="0.1">

        <div class="flex items-center justify-between px-3 py-2 bg-grey-20 border-b text-center">
            {{ modalTitle }}
            <button class="text-grey-60 hover:text-grey-90 text-lg" @click="cancel">&times;</button>
        </div>

        <div class="publish-fields">

            <form-group
                v-if="!initialDirectory"
                handle="directory"
                :display="__('Folder Name')"
                :instructions="__('We recommend avoiding spaces and special characters to keep your URLs clean.')"
                :errors="errors.directory"
                autofocus
                v-model="directory"
                :required="true"
            />

            <div class="px-3 pb-3">
                <button
                    class="btn btn-primary"
                    @click.prevent="submit"
                    v-text="buttonText"
                />
            </div>

        </div>

    </modal>

</template>

<script>
export default {

    props: {
        initialDirectory: String,
        container: Object,
        path: String,
    },

    data() {
        return {
            directory: this.initialDirectory,
            errors: {},
        }
    },

    methods: {

        cancel() {
            this.$emit('closed');
        },

        handleErrors(e) {
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.errors = errors;
                this.$notify.error(message, { timeout: 2000 });
                this.saving = false;
            } else {
                this.$notify.error(__('Something went wrong'));
            }
        }

    }

}
</script>
