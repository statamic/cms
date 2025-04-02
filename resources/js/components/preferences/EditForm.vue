<template>
    <publish-container
        ref="container"
        :name="name"
        :blueprint="blueprint"
        :values="currentValues"
        @updated="currentValues = $event"
        reference="collection"
        :meta="meta"
        :errors="errors"
        v-slot="{ setFieldValue, setFieldMeta }"
    >
        <div>
            <ui-header :title="title">

                <ui-button-group>
                    <ui-button
                        type="submit"
                        variant="primary"
                        :text="__('Save')"
                        @click="save"
                    />

                    <ui-dropdown align="end" v-if="hasSaveAsOptions">
                        <template #trigger>
                            <ui-button icon="chevron-down" variant="primary" />
                        </template>
                        <ui-dropdown-menu>
                            <ui-dropdown-label>{{ __('Save to') }}...</ui-dropdown-label>
                            <ui-dropdown-item
                                v-for="option in saveAsOptions"
                                :key="option.url"
                                :text="option.label"
                                @click="saveAs(option.url)"
                            />
                        </ui-dropdown-menu>
                    </ui-dropdown>
                </ui-button-group>
            </ui-header>

            <publish-tabs
                @updated="setFieldValue"
                @meta-updated="setFieldMeta"
                :enable-sidebar="hasSidebar"
                :read-only="readOnly"
            />
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
        readOnly: { type: Boolean, default: false },
        reloadOnSave: { type: Boolean, default: false },
        saveAsOptions: { type: Array, default: () => [] },
    },

    data() {
        return {
            saving: false,
            currentValues: this.values,
            error: null,
            errors: {},
            hasSidebar: this.blueprint.tabs.map((tab) => tab.handle).includes('sidebar'),
        };
    },

    computed: {
        hasSaveAsOptions() {
            return this.saveAsOptions.length;
        },

        isDirty() {
            return this.$dirty.has(this.name);
        },
    },

    methods: {
        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            this.saveAs(this.action);
        },

        saveAs(url) {
            this.saving = true;
            this.clearErrors();

            this.$axios
                .patch(url, this.currentValues)
                .then(() => {
                    this.$refs.container.saved();
                    location.reload();
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
                const message = data_get(e, 'response.data.message');
                this.$toast.error(message || e);
                console.log(e);
            }
        },
    },

    created() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },

    watch: {
        saving(saving) {
            this.$progress.loading('preferences-edit-form', saving);
        },
    },
};
</script>
