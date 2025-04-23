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
            <breadcrumbs v-if="breadcrumbs" :crumbs="breadcrumbs" />

            <div class="mb-6 flex items-center">
                <h1 class="flex-1">{{ title }}</h1>

                <div class="ltr:ml-4 ltr:text-left rtl:mr-4 rtl:text-right" :class="{ 'btn-group': hasSaveAsOptions }">
                    <button
                        class="btn-primary ltr:pl-4 rtl:pr-4"
                        :class="{ disabled: !isDirty }"
                        :disabled="!isDirty"
                        @click="save"
                        v-text="__('Save')"
                    />

                    <dropdown-list v-if="hasSaveAsOptions" class="ltr:ml-0 rtl:mr-0">
                        <template #trigger>
                            <button class="btn-primary flex items-center ltr:rounded-l-none rtl:rounded-r-none">
                                <svg-icon name="micro/chevron-down-xs" class="w-2" />
                            </button>
                        </template>
                        <h6 class="p-2">{{ __('Save to') }}...</h6>
                        <dropdown-item v-for="option in saveAsOptions" :key="option.url" @click="saveAs(option.url)">
                            <div class="flex items-start ltr:pr-4 rtl:pl-4">
                                <svg-icon
                                    :name="option.icon"
                                    class="w-4 shrink-0 text-gray group-hover:text-white ltr:mr-2 rtl:ml-2"
                                />
                                <span class="whitespace-normal">{{ option.label }}</span>
                            </div>
                        </dropdown-item>
                    </dropdown-list>
                </div>
            </div>

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
                    this.$nextTick(() => location.reload());
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
