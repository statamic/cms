<template>
    <publish-container
        v-if="blueprint"
        class="max-w-5xl mx-auto"
        ref="container"
        name="collection"
        reference="collection"
        :blueprint="blueprint"
        :values="values"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
        v-slot="{ setFieldValue, setFieldMeta }"
    >
        <div>
            <Header :title="__(editTitle ?? 'Configure Navigation')" icon="cog">
                <Button variant="primary" @click="submit">{{ __('Save') }}</Button>
            </Header>
            <configure-tabs @updated="setFieldValue" @meta-updated="setFieldMeta" :enable-sidebar="false" />
        </div>
    </publish-container>
</template>

<script>
import { Header, Button } from '@statamic/ui';

export default {
    components: {
        Header,
        Button,
    },

    props: {
        blueprint: Object,
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
};
</script>
