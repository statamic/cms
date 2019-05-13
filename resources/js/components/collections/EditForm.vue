<template>

    <publish-container
        v-if="blueprint"
        ref="container"
        name="collection"
        :fieldset="blueprint"
        :values="values"
        reference="collection"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
    >
        <div>
            <div class="flex items-center mb-3">
                <h1 class="flex-1" v-text="title" />
                <button type="submit" class="btn btn-primary" @click="submit">{{ __('Save') }}</button>
            </div>

            <publish-sections @updated="setValue" />
        </div>
    </publish-container>

</template>

<script>
export default {

    props: {
        blueprint: Object,
        initialValues: Object,
        meta: Object,
        initialTitle: String,
        url: String
    },

    data() {
        return {
            title: this.initialTitle,
            values: this.initialValues,
            error: null,
            errors: {},
        }
    },

    methods: {

        setValue(handle, value) {
            this.values[handle] = value;
        },

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            this.saving = true;
            this.clearErrors();

            this.$axios.patch(this.url, this.values).then(response => {
                this.saving = false;
                this.title = response.data.title;
                this.$notify.success('Saved');
                this.$refs.container.saved();
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$notify.error(message);
            } else {
                this.$notify.error('Something went wrong');
            }
        },

    }

}
</script>
