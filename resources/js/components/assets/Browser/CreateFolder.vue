<template>

    <confirmation-modal
        name="folder-editor"
        :title="modalTitle"
        :busy="submitting"
        @cancel="cancel"
        @confirm="submit"
    >

        <div class="publish-fields @container">

            <form-group
                v-if="!initialDirectory"
                handle="directory"
                :display="__('Folder Name')"
                :errors="errors.directory"
                :instructions="__('messages.asset_folders_directory_instructions')"
                :focus="true"
                :required="true"
                :config="{ debounce: false }"
                v-model="directory"
            />

        </div>

    </confirmation-modal>

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
            modalTitle: __('Create Folder'),
            buttonText: __('Create'),
            directory: this.initialDirectory,
            errors: {},
            submitting: false,
        }
    },

    methods: {

        cancel() {
            this.$emit('closed');
        },

        submit() {
            const url = cp_url(`asset-containers/${this.container.id}/folders`);
            const payload = {
                path: this.path,
                directory: this.directory,
                title: this.title
            };

            this.submitting = true;

            this.$axios.post(url, payload).then(response => {
                this.$toast.success(__('Folder created'));
                this.$emit('created', response.data);
            }).catch(e => {
                this.handleErrors(e);
            }).finally(() => {
                this.submitting = false;
            });
        },

        handleErrors(e) {
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.errors = errors;
                this.$toast.error(message);
                this.saving = false;
            } else {
                this.$toast.error(__('Something went wrong'));
            }
        }

    },

    created() {
        this.$keys.bindGlobal('esc', this.cancel)
    },

}
</script>
