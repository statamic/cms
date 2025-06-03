<template>
    <publish-container
        v-if="blueprint"
        ref="container"
        name="sites"
        reference="sites"
        :blueprint="blueprint"
        :values="values"
        :meta="meta"
        :errors="errors"
        @updated="values = $event"
        v-slot="{ setFieldValue, setFieldMeta }"
    >
        <div>
            <Header :title="pageTitle" icon="site">
                <Button type="submit" variant="primary" @click="submit">{{ __('Save') }}</Button>
            </Header>
            <publish-tabs @updated="setFieldValue" @meta-updated="setFieldMeta" :enable-sidebar="false" />
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

    computed: {
        pageTitle() {
            return this.$config.get('multisiteEnabled') ? __('Configure Sites') : __('Configure Site');
        },

        initialSiteHandles() {
            return this.$config.get('multisiteEnabled')
                ? this.initialValues.sites.map((site) => site.handle)
                : [this.initialValues.handle];
        },

        currentSiteHandles() {
            return this.$config.get('multisiteEnabled')
                ? this.values.sites.map((site) => site.handle)
                : [this.values.handle];
        },

        initialHandleChanged() {
            return this.initialSiteHandles.filter((handle) => !this.currentSiteHandles.includes(handle)).length > 0;
        },

        initialHandleChangedWarning() {
            return __('Warning! Changing a site handle may break existing site content!');
        },
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            if (this.initialHandleChanged && !confirm(this.initialHandleChangedWarning)) {
                return;
            }

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
