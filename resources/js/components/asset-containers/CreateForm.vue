<template>
    <publish-container
        v-if="blueprint"
        ref="container"
        name="collection"
        :blueprint="blueprint"
        :values="values"
        reference="collection"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
        v-slot="{ setFieldValue }"
    >
        <div>
            <div class="mb-6 flex items-center">
                <h1 class="flex-1">
                    <small class="subhead block">
                        <a :href="listingUrl" v-text="__('Assets')" />
                    </small>
                    {{ title }}
                </h1>
                <button type="submit" class="btn-primary" @click="submit">{{ __('Save') }}</button>
            </div>

            <configure-tabs @updated="setFieldValue" :enable-sidebar="false" />
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
        listingUrl: String,
    },

    data() {
        return {
            title: this.initialTitle,
            values: this.initialValues,
            error: null,
            errors: {},
        };
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            this.saving = true;
            this.clearErrors();

            this.$axios
                .post(this.url, this.values)
                .then((response) => {
                    this.$refs.container.saved();
                    this.$nextTick(() => (window.location = response.data.redirect));
                })
                .catch((e) => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error(__('Something went wrong'));
            }
        },
    },
};
</script>
