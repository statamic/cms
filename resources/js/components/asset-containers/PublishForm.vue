<template>

    <publish-container
        v-if="blueprint"
        ref="container"
        name="asset-container"
        :blueprint="blueprint"
        :values="values"
        reference="asset-container"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
    >
        <div slot-scope="{ setFieldValue }">
            <div class="flex items-center mb-3">
                <h1 class="flex-1" v-text="title" />
                <button type="submit" class="btn btn-primary" @click="submit">{{ __('Save') }}</button>
            </div>

            <publish-sections @updated="setFieldValue" />
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
        url: String,
        action: String,
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

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            this.saving = true;
            this.clearErrors();

            this.$axios[this.action](this.url, this.values).then(response => {
                this.saving = false;
                this.submitComplete(response);
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error('Something went wrong');
            }
        },

    }

}
</script>
