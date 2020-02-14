<template>

    <div>
        <header class="mb-3">
            <breadcrumb :url="globalsUrl" :title="__('Globals')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <div class="pt-px text-2xs text-grey-60 ml-2 flex" v-if="! canEdit">
                    <svg-icon name="lock" class="w-4 mr-sm -mt-sm" /> {{ __('Read Only') }}
                </div>

                <configure-set
                    class="ml-2"
                    :save-url="configureSaveUrl"
                    :delete-url="deleteUrl"
                    :globals-url="globalsUrl"
                    :id="id"
                    :initial-title="initialTitle"
                    :initial-handle="initialHandle"
                    :initial-blueprint="initialBlueprintHandle"
                    :can-configure="canConfigure"
                    :can-delete="canDelete"
                ></configure-set>

                <v-select
                    v-if="localizations.length > 1"
                    :value="activeLocalization"
                    label="name"
                    :clearable="false"
                    :options="localizations"
                    :searchable="false"
                    :multiple="false"
                    @input="localizationSelected"
                    class="w-48 ml-2"
                >
                    <template slot="option" slot-scope="option">
                        <div class="flex items-center" v-tooltip="localizationStatusText(option)">
                            <loading-graphic :size="14" text="" class="flex -ml-1" v-if="localizing === option.handle" />
                            <span class="little-dot mr-1" :class="{
                                'bg-green': option.published,
                                'bg-grey-50': !option.published,
                                'bg-red': !option.exists
                            }" />
                            {{ option.name }}
                            <svg-icon name="flag" class="h-3 w-3 ml-sm text-grey" v-if="option.origin" />
                            <svg-icon name="check" class="h-3 w-3 ml-sm text-grey" v-if="option.active" />
                        </div>
                    </template>
                </v-select>

                <button
                    v-if="canEdit"
                    class="btn-primary min-w-100 ml-2"
                    :class="{ 'opacity-25': !canSave }"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="__('Save')" />

                <slot name="action-buttons-right" />
            </div>
        </header>

        <div v-if="fieldset.empty" class="text-center mt-5 border-2 border-dashed rounded-lg px-4 py-8">
            <div class="max-w-md mx-auto opacity-50">
                <span v-html="globeSvg" />
                <h1 class="my-3">This Global Set has no fields.</h1>
                <p>You can add fields to the Blueprint, or you can manually add variables to the set itself.</p>
            </div>
        </div>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :blueprint="fieldset"
            :values="values"
            :reference="initialReference"
            :meta="meta"
            :errors="errors"
            :site="site"
            :localized-fields="localizedFields"
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
import ConfigureSet from './Configure.vue';

export default {

    components: {
        ConfigureSet
    },

    props: {
        publishContainer: String,
        id: String,
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
        configureSaveUrl: String,
        deleteUrl: String,
        canEdit: Boolean,
        canConfigure: Boolean,
        canDelete: Boolean,
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
        },

        globeSvg() {
            return require(`!!html-loader!./../../../svg/empty/global.svg`)
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

            const payload = { ...this.values, ...{
                blueprint: this.fieldset.handle,
                _localized: this.localizedFields,
            }};

            this.$axios[this.method](this.actions.save, payload).then(response => {
                this.saving = false;
                if (!this.isCreating) this.$toast.success('Saved');
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
                this.$toast.error('Something went wrong');
            }
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (this.isDirty) {
                if (! confirm('Are you sure? Unsaved changes will be lost.')) {
                    return;
                }
            }

            this.localizing = localization.handle;

            if (localization.exists) {
                this.editLocalization(localization);
            } else {
                this.createLocalization(localization);
            }
        },

        editLocalization(localization) {
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
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.clearDirtyState());
            })
        },

        createLocalization(localization) {
            // TODO: This is obviously a horrible way to get the url. Do it better.
            let url = this.activeLocalization.url;
            url = url.includes('?site=') ? url.replace('?site=', '/localize?site=') : `${url}/localize`;

            const payload = {
                origin: this.originLocalization.handle,
                target: localization.handle
            };

            this.$axios.post(url, payload).then(response => {
                this.editLocalization(response.data);
            });
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
            if (! confirm('Are you sure? This field\'s value will be replaced by the value in the original entry.'))
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
