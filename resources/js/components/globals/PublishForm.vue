<template>

    <div>
        <header class="mb-3">
            <breadcrumb :url="globalsUrl" :title="__('Globals')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <div class="pt-px text-2xs text-grey-60 ml-2 flex" v-if="! canEdit">
                    <svg-icon name="lock" class="w-4 mr-sm -mt-sm" /> {{ __('Read Only') }}
                </div>

                <dropdown-list v-if="canConfigure || canEditBlueprint" class="mr-1">
                    <dropdown-item v-if="canConfigure" v-text="__('Configure')" :redirect="configureUrl" />
                    <dropdown-item v-if="canEditBlueprint" :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
                </dropdown-list>

                <site-selector
                    v-if="localizations.length > 1"
                    class="mr-2"
                    :sites="localizations"
                    :value="site"
                    @input="localizationSelected"
                />

                <button
                    v-if="canEdit"
                    class="btn-primary min-w-100"
                    :class="{ 'opacity-25': !canSave }"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="__('Save')" />

                <slot name="action-buttons-right" />
            </div>
        </header>

        <div v-if="fieldset.empty" class="text-center mt-5 border-2 border-dashed rounded-lg px-4 py-8">
            <div class="max-w-md mx-auto opacity-50">
                <h1 class="my-3" v-text="__('This Global Set has no fields.')" />
                <p v-text="__('messages.global_set_no_fields_description')" />
            </div>
        </div>

        <publish-container
            v-if="fieldset && !fieldset.empty"
            ref="container"
            :name="publishContainer"
            :blueprint="fieldset"
            :values="values"
            :reference="initialReference"
            :meta="meta"
            :errors="errors"
            :site="site"
            :localized-fields="localizedFields"
            :is-root="isRoot"
            @updated="values = $event"
        >
            <div slot-scope="{ container, components, setFieldMeta }">
                <component
                    v-for="component in components"
                    :key="component.name"
                    :is="component.name"
                    :container="container"
                    v-bind="component.props"
                />
                <publish-sections
                    :read-only="! canEdit"
                    :syncable="hasOrigin"
                    :can-toggle-labels="true"
                    :enable-sidebar="false"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    @synced="syncField"
                    @desynced="desyncField"
                    @focus="container.$emit('focus', $event)"
                    @blur="container.$emit('blur', $event)"
                />
            </div>
        </publish-container>
    </div>

</template>

<script>
import SiteSelector from '../SiteSelector.vue';
import HasHiddenFields from '../publish/HasHiddenFields';

export default {

    mixins: [
        HasHiddenFields,
    ],

    components: {
        SiteSelector
    },

    props: {
        publishContainer: String,
        initialReference: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialHandle: String,
        initialBlueprintHandle: String,
        initialLocalizations: Array,
        initialLocalizedFields: Array,
        initialHasOrigin: Boolean,
        initialOriginValues: Object,
        initialOriginMeta: Object,
        initialSite: String,
        globalsUrl: String,
        initialActions: Object,
        method: String,
        isCreating: Boolean,
        initialReadOnly: Boolean,
        initialIsRoot: Boolean,
        canEdit: Boolean,
        canConfigure: Boolean,
        configureUrl: String,
        canEditBlueprint: Boolean,
    },

    data() {
        return {
            actions: this.initialActions,
            saving: false,
            localizing: false,
            fieldset: this.initialFieldset,
            title: this.initialTitle,
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            localizations: _.clone(this.initialLocalizations),
            localizedFields: this.initialLocalizedFields,
            hasOrigin: this.initialHasOrigin,
            originValues: this.initialOriginValues || {},
            originMeta: this.initialOriginMeta || {},
            site: this.initialSite,
            error: null,
            errors: {},
            isRoot: this.initialIsRoot,
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        somethingIsLoading() {
            return ! this.$progress.isComplete();
        },

        canSave() {
            return this.canEdit && this.isDirty && !this.somethingIsLoading;
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        },

        originLocalization() {
            return _.findWhere(this.localizations, { origin: true });
        }

    },

    watch: {

        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-global-publish-form`, saving);
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            if (!this.canSave) return;

            this.saving = true;
            this.clearErrors();

            const payload = { ...this.visibleValues, ...{
                blueprint: this.fieldset.handle,
                _localized: this.localizedFields,
            }};

            this.$axios[this.method](this.actions.save, payload).then(response => {
                this.saving = false;
                if (!this.isCreating) this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.$nextTick(() => this.$emit('saved', response));
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
                this.$toast.error(__('Something went wrong'));
            }
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (this.isDirty) {
                if (! confirm(__('Are you sure? Unsaved changes will be lost.'))) {
                    return;
                }
            }

            this.localizing = localization.handle;

            if (this.publishContainer === 'base') {
                window.history.replaceState({}, '', localization.url);
            }

            this.$axios.get(localization.url).then(response => {
                const data = response.data;
                this.values = data.values;
                this.originValues = data.originValues;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.localizedFields = data.localizedFields;
                this.hasOrigin = data.hasOrigin;
                this.actions = data.actions;
                this.fieldset = data.blueprint;
                this.isRoot = data.isRoot;
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.clearDirtyState());
            })
        },

        localizationStatusText(localization) {
            return localization.exists
                ? 'This global set exists in this site.'
                : 'This global set does not exist for this site.';
        },

        setFieldValue(handle, value) {
            if (this.hasOrigin) this.desyncField(handle);

            this.$refs.container.setFieldValue(handle, value);
        },

        syncField(handle) {
            if (! confirm(__('Are you sure? This field\'s value will be replaced by the value in the original entry.')))
                return;

            this.localizedFields = this.localizedFields.filter(field => field !== handle);
            this.$refs.container.setFieldValue(handle, this.originValues[handle]);

            // Update the meta for this field. For instance, a relationship field would have its data preloaded into it.
            // If you sync the field, the preloaded data would be outdated and an ID would show instead of the titles.
            this.meta[handle] = this.originMeta[handle];
        },

        desyncField(handle) {
            if (!this.localizedFields.includes(handle))
                this.localizedFields.push(handle);

            this.$refs.container.dirty();
        },

    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));
    }

}
</script>
