<template>
    <publish-container
        class="max-w-5xl mx-auto"
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
        data-cards-wrap="fields"
    >
        <div>
            <Header :title="__(editTitle ?? 'Configure Collection')" icon="cog">
                <Button variant="primary" @click="submit">{{ __('Save') }}</Button>
            </Header>
            <configure-tabs
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
                :enable-sidebar="false"
            />
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

    provide() {
        return {
            wrapFieldsInCards: true,
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
