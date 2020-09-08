<template>

    <publish-container
        ref="container"
        :name="name"
        :blueprint="blueprint"
        v-model="currentValues"
        reference="collection"
        :meta="meta"
        :errors="errors"
        v-slot="{ setFieldValue, setFieldMeta }"
    >
        <div>
            <breadcrumbs v-if="breadcrumbs" :crumbs="breadcrumbs" />

            <div class="flex items-center mb-3">
                <h1 class="flex-1">{{ title }}</h1>
                <button v-if="action" type="submit" class="btn-primary" @click="submit">{{ __('Save') }}</button>
            </div>

            <publish-sections
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
                :can-toggle-labels="canToggleLabels"
                :enable-sidebar="hasSidebar"
                :read-only="readOnly" />
        </div>
    </publish-container>

</template>

<script>
export default {

    props: {
        blueprint: { required: true, type: Object },
        meta: { required: true, type: Object },
        values: { required: true, type: Object },
        title: { required: true, type: String },
        name: { type: String, default: 'base' },
        breadcrumbs: Array,
        action: String,
        method: { type: String, default: 'post' },
        canToggleLabels: { type: Boolean, default: true },
        readOnly: { type: Boolean, default: false }
    },

    data() {
        return {
            currentValues: this.values,
            error: null,
            errors: {},
            hasSidebar: this.blueprint.sections.map(section => section.handle).includes('sidebar'),
        }
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit() {
            if (!this.action) return;

            this.saving = true;
            this.clearErrors();

            this.$axios[this.method](this.action, this.currentValues).then(response => {
                this.saving = false;
                this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.$emit('saved', response);
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
                const message = data_get(e, 'response.data.message');
                this.$toast.error(message || e);
                console.log(e);
            }
        },

    },

    created() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.submit();
        });
    },

}
</script>
