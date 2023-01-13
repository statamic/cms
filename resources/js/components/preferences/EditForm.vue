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

                <div class="ml-2 text-left" :class="{ 'btn-group': hasSaveAsOptions }">
                    <button
                        class="btn-primary pl-2"
                        :class="{ 'disabled': !isDirty }"
                        :disabled="!isDirty"
                        @click="save"
                        v-text="__('Save')" />

                    <dropdown-list v-if="hasSaveAsOptions" class="ml-0">
                        <template #trigger>
                            <button class="btn-primary rounded-l-none flex items-center">
                                <svg-icon name="chevron-down-xs" class="w-2" />
                            </button>
                        </template>
                        <h6 class="p-1">{{ __('Save to') }}...</h6>
                        <dropdown-item v-for="option in saveAsOptions" :key="option.url" @click="saveAs(option.url)">
                            <div class="flex items-start pr-2">
                                <svg-icon :name="option.icon" class="text-grey flex-shrink-0 mr-1 w-4 group-hover:text-white" />
                                <span class="whitespace-normal">{{ option.label }}</span>
                            </div>
                        </dropdown-item>
                    </dropdown-list>
                </div>
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
        canToggleLabels: { type: Boolean, default: true },
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
            hasSidebar: this.blueprint.sections.map(section => section.handle).includes('sidebar'),
        }
    },

    computed: {

        hasSaveAsOptions() {
            return this.saveAsOptions.length;
        },

        isDirty() {
            return this.$dirty.has(this.name);
        }

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
                .catch(e => this.handleAxiosError(e));
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
            this.save();
        });
    },

    watch: {

        saving(saving) {
            this.$progress.loading('preferences-edit-form', saving);
        }

    },

}
</script>
