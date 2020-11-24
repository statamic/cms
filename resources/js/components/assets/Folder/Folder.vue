<template>

    <modal name="folder-editor">

        <div class="flex items-center justify-between px-3 py-2 bg-grey-20 border-b text-center">
            {{ modalTitle }}
            <button type="button"
                tabindex="-1"
                class="btn-close"
                aria-label="Close"
                @click="cancel"
                v-html="'&times'" />
        </div>

        <publish-fields-container>

            <form-group
                v-if="!initialDirectory"
                handle="directory"
                :display="__('Folder Name')"
                :errors="errors.directory"
                :instructions="__('messages.asset_folders_directory_instructions')"
                :focus="true"
                :required="true"
                v-model="directory"
            />

            <div class="px-3 pb-3">
                <button
                    class="btn-primary"
                    @click.prevent="submit"
                    v-text="buttonText"
                />
            </div>

        </publish-fields-container>

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
                this.$toast.error(message);
                this.saving = false;
            } else {
                this.$toast.error(__('Something went wrong'));
            }
        }

    },

    created() {
        this.$keys.bindGlobal('enter', this.submit)
        this.$keys.bindGlobal('esc', this.cancel)
    },

}
</script>
