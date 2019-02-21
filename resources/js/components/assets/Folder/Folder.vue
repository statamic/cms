<template>

    <modal name="folder-editor" :pivotY="0.1">

        <div class="flex items-center justify-between p-3 bg-grey-20 border-b text-center">
            {{ modalTitle }}
            <button class="text-grey" @click="cancel">&times;</button>
        </div>

        <div class="publish-fields">

            <form-group
                v-if="!initialDirectory"
                handle="directory"
                :display="__('Directory')"
                :instructions="__('The name of the directory to be created in the filesystem.')"
                :errors="errors.directory"
                autofocus
                v-model="directory"
                :required="true"
            />

            <form-group
                handle="title"
                :display="__('Title')"
                :instructions="__('The display name of the folder, if different from the directory name.')"
                :errors="errors.title"
                v-model="title"
            />

            <div class="p-3">
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
        initialTitle: String,
        container: Object,
        path: String,
    },

    data() {
        return {
            directory: this.initialDirectory,
            title: this.initialTitle,
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
