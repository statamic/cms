<template>
    <publish-container
        v-if="blueprint"
        ref="container"
        name="collection"
        reference="collection"
        :blueprint="blueprint"
        :values="values"
        :meta="meta"
        :errors="errors"
        :site="site"
        @updated="values = $event"
        v-slot="{ setFieldValue, setFieldMeta }"
    >
        <div>
            <header class="mb-6">
                <breadcrumb :url="url" :title="values.title" />
                <div class="flex items-center">
                    <h1 class="flex-1" v-text="__(editTitle ?? 'Configure Collection')" />
                    <button type="submit" class="btn-primary" @click="submit">{{ __('Save') }}</button>
                </div>
            </header>
            <configure-tabs @updated="setFieldValue" @meta-updated="setFieldMeta" :enable-sidebar="false" />
        </div>
    </publish-container>
</template>

<script>
export default {
    props: {
        blueprint: Object,
        editTitle: String,
        initialValues: Object,
        meta: Object,
        url: String,
    },

    data() {
        return {
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
                .patch(this.url, this.values)
                .then((response) => {
                    this.saving = false;
                    this.$toast.success(__('Saved'));
                    this.$refs.container.saved();
                })
                .catch((e) => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error(__('Something went wrong'));
            }
        },
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.submit();
        });
    },

    computed: {
        site() {
            return this.$config.get('selectedSite');
        },
    },
};
</script>
