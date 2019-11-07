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
                this.$toast.error(message);
                this.saving = false;
            } else {
                this.$toast.error(__('Something went wrong'));
            }
        }

    },

    created() {
        // Allow key commands with a focused input
        this.$mousetrap.prototype.stopCallback = (e) => {
            return ! ['enter', 'escape'].includes(e.code.toLowerCase());
        }

        this.$mousetrap.bind('enter', this.submit)
        this.$mousetrap.bind('esc', this.cancel)
    },

}
</script>
