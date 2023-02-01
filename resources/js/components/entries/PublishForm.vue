<template>

    <div>
        <breadcrumb v-if="breadcrumbs" :url="breadcrumbs[1].url" :title="breadcrumbs[1].text" />

        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <div class="flex items-center">
                    <span v-if="! isCreating" class="little-dot mr-1" :class="activeLocalization.status" v-tooltip="activeLocalization.status" />
                    <span v-html="$options.filters.striptags(title)" />
                </div>
            </h1>

            <dropdown-list class="mr-2" v-if="canEditBlueprint">
                <dropdown-item :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
            </dropdown-list>

            <div class="pt-px text-2xs text-grey-60 flex mr-2" v-if="readOnly">
                <svg-icon name="lock" class="w-4 mr-sm -mt-sm" /> {{ __('Read Only') }}
            </div>

            <div class="hidden md:flex items-center">

                <save-button-options
                    v-if="!readOnly"
                    :show-options="!revisionsEnabled && !isInline"
                    :button-class="saveButtonClass"
                    :preferences-prefix="preferencesPrefix"
                >
                    <button
                        :class="saveButtonClass"
                        :disabled="!canSave"
                        @click.prevent="save"
                        v-text="saveText"
                    />
                </save-button-options>

                <button
                    v-if="revisionsEnabled && !isCreating"
                    class="ml-2 btn-primary flex items-center"
                    :disabled="!canPublish"
                    @click="confirmingPublish = true">
                    <span>{{ __('Publish') }}…</span>
                </button>
            </div>

            <slot name="action-buttons-right" />
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
            :is-root="isRoot"
            :track-dirty-state="trackDirtyState"
            @updated="values = $event"
        >
            <live-preview
                slot-scope="{ container, components, setFieldMeta }"
                :name="publishContainer"
                :url="livePreviewUrl"
                :previewing="isPreviewing"
                :targets="previewTargets"
                :values="values"
                :blueprint="fieldset.handle"
                @opened-via-keyboard="openLivePreview"
                @closed="closeLivePreview"
            >

                <div>
                    <component
                        v-for="component in components"
                        :key="component.id"
                        :is="component.name"
                        :container="container"
                        v-bind="component.props"
                        v-on="component.events"
                    />

                    <transition name="live-preview-sections-drop">
                        <publish-sections
                            v-show="sectionsVisible"
                            :read-only="readOnly"
                            :syncable="hasOrigin"
                            :can-toggle-labels="true"
                            @updated="setFieldValue"
                            @meta-updated="setFieldMeta"
                            @synced="syncField"
                            @desynced="desyncField"
                            @focus="container.$emit('focus', $event)"
                            @blur="container.$emit('blur', $event)"
                        >
                            <template #actions="{ shouldShowSidebar }">

                                <div v-if="collectionHasRoutes" :class="{ 'hi': !shouldShowSidebar }">

                                    <div class="p-2 flex items-center -mx-1">
                                        <button
                                            class="flex items-center justify-center btn-flat w-full mx-1 px-1"
                                            v-if="isBase && livePreviewUrl"
                                            @click="openLivePreview">
                                            <svg-icon name="synchronize" class="w-5 h-5 mr-1" />
                                            <span>{{ __('Live Preview') }}</span>
                                        </button>
                                        <a
                                            class="flex items-center justify-center btn-flat w-full mx-1 px-1"
                                            v-if="permalink"
                                            :href="permalink"
                                            target="_blank">
                                            <svg-icon name="external-link" class="w-4 h-4 mr-1" />
                                            <span>{{ __('Visit URL') }}</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="flex items-center border-t justify-between px-2 py-1" v-if="!revisionsEnabled">
                                    <label v-text="__('Published')" class="publish-field-label font-medium" />
                                    <toggle-input :value="published" :read-only="!canManagePublishState" @input="setFieldValue('published', $event)" />
                                </div>

                                <div class="border-t p-2" v-if="revisionsEnabled && !isCreating">
                                    <label class="publish-field-label font-medium mb-1" v-text="__('Revisions')"/>
                                    <div class="mb-sm flex items-center" v-if="published">
                                        <span class="text-green w-6 text-center">&check;</span>
                                        <span class="text-2xs" v-text="__('Entry has a published version')"></span>
                                    </div>
                                    <div class="mb-sm flex items-center" v-else>
                                        <span class="text-orange w-6 text-center">!</span>
                                        <span class="text-2xs" v-text="__('Entry has not been published')"></span>
                                    </div>
                                    <div class="mb-sm flex items-center" v-if="!isWorkingCopy && published">
                                        <span class="text-green w-6 text-center">&check;</span>
                                        <span class="text-2xs" v-text="__('This is the published version')"></span>
                                    </div>
                                    <div class="mb-sm flex items-center" v-if="isDirty">
                                        <span class="text-orange w-6 text-center">!</span>
                                        <span class="text-2xs" v-text="__('Unsaved changes')"></span>
                                    </div>
                                    <button
                                            class="flex items-center justify-center mt-2 btn-flat px-1 w-full"
                                            v-if="!isCreating && revisionsEnabled"
                                            @click="showRevisionHistory = true">
                                            <svg-icon name="history" class="w-5 h-5 mr-1" />
                                            <span>{{ __('View History') }}</span>
                                        </button>
                                </div>

                                <div class="p-2 border-t" v-if="localizations.length > 1">
                                    <label class="publish-field-label font-medium mb-1" v-text="__('Sites')" />
                                    <div
                                        v-for="option in localizations"
                                        :key="option.handle"
                                        class="text-sm flex items-center -mx-2 px-2 py-1 cursor-pointer"
                                        :class="option.active ? 'bg-blue-100' : 'hover:bg-grey-20'"
                                        @click="localizationSelected(option)"
                                    >
                                        <div class="flex-1 flex items-center" :class="{ 'line-through': !option.exists }">
                                            <span class="little-dot mr-1" :class="{
                                                'bg-green': option.published,
                                                'bg-grey-50': !option.published,
                                                'bg-red': !option.exists
                                            }" />
                                            {{ option.name }}
                                            <loading-graphic
                                                :size="14"
                                                text=""
                                                class="ml-1"
                                                v-if="localizing && localizing.handle === option.handle" />
                                        </div>
                                        <div class="badge-sm bg-orange" v-if="option.origin" v-text="__('Origin')" />
                                        <div class="badge-sm bg-blue" v-if="option.active" v-text="__('Active')" />
                                        <div class="badge-sm bg-purple" v-if="option.root && !option.origin && !option.active" v-text="__('Root')" />
                                    </div>
                                </div>

                            </template>
                        </publish-sections>
                    </transition>
                </div>
                <template v-slot:buttons>
                   <button
                        v-if="!readOnly"
                        class="ml-2"
                        :class="{
                            'btn': revisionsEnabled,
                            'btn-primary': isCreating || !revisionsEnabled,
                        }"
                        :disabled="!canSave"
                        @click.prevent="save"
                        v-text="saveText">
                    </button>

                    <button
                        v-if="revisionsEnabled && !isCreating"
                        class="ml-2 btn-primary flex items-center"
                        :disabled="!canPublish"
                        @click="confirmingPublish = true">
                        <span v-text="__('Publish')" />
                        <svg-icon name="chevron-down-xs" class="ml-1 w-2" />
                    </button>
                </template>
            </live-preview>
        </publish-container>

        <div class="md:hidden mt-3 flex items-center">
            <button
                v-if="!readOnly"
                class="btn-lg"
                :class="{
                    'btn-primary w-full': ! revisionsEnabled,
                    'btn w-1/2 mr-2': revisionsEnabled,
                }"
                :disabled="!canSave"
                @click.prevent="save"
                v-text="__(revisionsEnabled ? 'Save Changes' : 'Save')" />

            <button
                v-if="revisionsEnabled"
                class="ml-1 btn btn-lg justify-center btn-primary flex items-center w-1/2"
                :disabled="!canPublish"
                @click="confirmingPublish = true">
                <span v-text="__('Publish')" />
                <svg-icon name="chevron-down-xs" class="ml-1 w-2" />
            </button>
        </div>

        <stack name="revision-history" v-if="showRevisionHistory" @closed="showRevisionHistory = false" :narrow="true">
            <revision-history
                slot-scope="{ close }"
                :index-url="actions.revisions"
                :restore-url="actions.restore"
                :reference="initialReference"
                @closed="close"
            />
        </stack>

        <publish-actions
            v-if="confirmingPublish"
            :actions="actions"
            :published="published"
            :collection="collectionHandle"
            :reference="initialReference"
            :publish-container="publishContainer"
            :can-manage-publish-state="canManagePublishState"
            @closed="confirmingPublish = false"
            @saving="saving = true"
            @saved="publishActionCompleted"
        />

        <confirmation-modal
            v-if="selectingOrigin"
            :title="__('Create Localization')"
            :buttonText="__('Create')"
            @cancel="cancelLocalization()"
            @confirm="createLocalization(localizing)"
        >
            <div class="publish-fields">
                <div class="form-group publish-field field-w-full">
                    <label v-text="__('Origin')" />
                    <div class="help-block -mt-1" v-text="__('messages.entry_origin_instructions')"></div>
                    <select-input
                        v-model="selectedOrigin"
                        :options="originOptions"
                        :placeholder="false"
                    />
                </div>
            </div>

        </confirmation-modal>
    </div>

</template>


<script>
import PublishActions from './PublishActions';
import SaveButtonOptions from '../publish/SaveButtonOptions';
import RevisionHistory from '../revision-history/History';
import HasPreferences from '../data-list/HasPreferences';
import HasHiddenFields from '../publish/HasHiddenFields';

export default {

    mixins: [
        HasPreferences,
        HasHiddenFields,
    ],

    components: {
        PublishActions,
        SaveButtonOptions,
        RevisionHistory,
    },

    props: {
        publishContainer: String,
        initialReference: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialLocalizations: Array,
        initialLocalizedFields: Array,
        originBehavior: String,
        initialHasOrigin: Boolean,
        initialOriginValues: Object,
        initialOriginMeta: Object,
        initialSite: String,
        initialIsWorkingCopy: Boolean,
        collectionHandle: String,
        breadcrumbs: Array,
        initialActions: Object,
        method: String,
        amp: Boolean,
        isCreating: Boolean,
        isInline: Boolean,
        initialReadOnly: Boolean,
        initialIsRoot: Boolean,
        initialPermalink: String,
        revisionsEnabled: Boolean,
        preloadedAssets: Array,
        canEditBlueprint: Boolean,
        canManagePublishState: Boolean,
        createAnotherUrl: String,
        listingUrl: String,
        collectionHasRoutes: Boolean,
        previewTargets: Array,
        autosaveInterval: Number,
    },

    data() {
        return {
            actions: this.initialActions,
            saving: false,
            localizing: false,
            trackDirtyState: true,
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
            selectingOrigin: false,
            selectedOrigin: null,
            isWorkingCopy: this.initialIsWorkingCopy,
            error: null,
            errors: {},
            isPreviewing: false,
            sectionsVisible: true,
            state: 'new',
            revisionMessage: null,
            showRevisionHistory: false,
            preferencesPrefix: `collections.${this.collectionHandle}`,

            // Whether it was published the last time it was saved.
            // Successful publish actions (if using revisions) or just saving (if not) will update this.
            // The current published value is inside the "values" object, and also accessible as a computed.
            initialPublished: this.initialValues.published,

            confirmingPublish: false,
            readOnly: this.initialReadOnly,
            isRoot: this.initialIsRoot,
            permalink: this.initialPermalink,

            saveKeyBinding: null,
            quickSaveKeyBinding: null,
            quickSave: false,
            isAutosave: false,
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
            return !this.readOnly && !this.somethingIsLoading;
        },

        canPublish() {
            if (!this.revisionsEnabled) return false;

            if (this.readOnly || this.isCreating || this.somethingIsLoading || this.isDirty) return false;

            return true;
        },

        published() {
            return this.values.published;
        },

        livePreviewUrl() {
            return _.findWhere(this.localizations, { active: true }).livePreviewUrl;
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

        saveText() {
            switch(true) {
                case this.revisionsEnabled:
                    return __('Save Changes');
                case this.isUnpublishing:
                    return __('Save & Unpublish');
                case this.isDraft:
                    return __('Save Draft');
                default:
                    return __('Save & Publish');
            }
        },

        isUnpublishing() {
            return this.initialPublished && ! this.published && ! this.isCreating;
        },

        isDraft() {
            return ! this.published;
        },

        saveButtonClass() {
            return {
                'btn': this.revisionsEnabled,
                'btn-primary': this.isCreating || ! this.revisionsEnabled,
            };
        },

        afterSaveOption() {
            return this.getPreference('after_save');
        },

        originOptions() {
            return this.localizations
                .filter(localization => localization.exists)
                .map(localization => ({
                    value: localization.handle,
                    label: localization.name,
                }));
        },

    },

    watch: {

        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-entry-publish-form`, saving);
        }

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            if (! this.canSave) {
                this.quickSave = false;
                return;
            }

            this.saving = true;
            this.clearErrors();

            setTimeout(() => this.runBeforeSaveHook(), 151); // 150ms is the debounce time for fieldtype updates
        },

        runBeforeSaveHook() {
            Statamic.$hooks.run('entry.saving', {
                collection: this.collectionHandle,
                values: this.values,
                container: this.$refs.container,
                storeName: this.publishContainer,
            })
            .then(this.performSaveRequest)
            .catch(error => {
                this.saving = false;
                this.$toast.error(error || 'Something went wrong');
            });
        },

        performSaveRequest() {
            // Once the hook has completed, we need to make the actual request.
            // We build the payload here because the before hook may have modified values.
            const payload = { ...this.visibleValues, ...{
                _blueprint: this.fieldset.handle,
                _localized: this.localizedFields,
            }};

            this.$axios[this.method](this.actions.save, payload).then(response => {
                this.saving = false;
                this.title = response.data.data.title;
                this.isWorkingCopy = true;
                if (this.isBase) {
                    document.title = this.title + ' ‹ ' + this.breadcrumbs[1].text + ' ‹ ' + this.breadcrumbs[0].text + ' ‹ Statamic';
                }
                if (!this.revisionsEnabled) this.permalink = response.data.data.permalink;
                if (!this.isCreating && !this.isAutosave) this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.runAfterSaveHook(response);
            }).catch(error => this.handleAxiosError(error));
        },

        runAfterSaveHook(response) {
            // Once the save request has completed, we want to run the "after" hook.
            // Devs can do what they need and we'll wait for them, but they can't cancel anything.
            Statamic.$hooks
                .run('entry.saved', {
                    collection: this.collectionHandle,
                    reference: this.initialReference,
                    response
                })
                .then(() => {
                    // If revisions are enabled, just emit event.
                    if (this.revisionsEnabled) {
                        this.$nextTick(() => this.$emit('saved', response));
                        return;
                    }

                    let nextAction = this.quickSave || this.isAutosave ? 'continue_editing' : this.afterSaveOption;

                    // If the user has opted to create another entry, redirect them to create page.
                    if (!this.isInline && nextAction === 'create_another') {
                        window.location = this.createAnotherUrl;
                    }

                    // If the user has opted to go to listing (default/null option), redirect them there.
                    else if (!this.isInline && nextAction === null) {
                        window.location = this.listingUrl;
                    }

                    // Otherwise, leave them on the edit form and emit an event. We need to wait until after
                    // the hooks are resolved because if this form is being shown in a stack, we only
                    // want to close it once everything's done.
                    else {
                        this.values = this.resetValuesFromResponse(response.data.data.values);
                        this.initialPublished = response.data.data.published;
                        this.activeLocalization.published = response.data.data.published;
                        this.activeLocalization.status = response.data.data.status;
                        this.$nextTick(() => this.$emit('saved', response));
                    }

                    this.quickSave = false;
                    this.isAutosave = false;
                }).catch(e => console.error(e));
        },

        confirmPublish() {
            if (this.canPublish) {
                this.confirmingPublish = true;
            }
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else if (e.response) {
                this.$toast.error(e.response.data.message);
            } else {
                this.$toast.error(e || 'Something went wrong');
            }
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (this.isDirty) {
                if (! confirm(__('Are you sure? Unsaved changes will be lost.'))) {
                    return;
                }
            }

            this.$dirty.remove(this.publishContainer);

            this.localizing = localization;

            if (localization.exists) {
                this.editLocalization(localization);
            } else if (this.localizations.length > 2 && this.originBehavior === 'select') {
                this.selectingOrigin = true;
            } else {
                this.createLocalization(localization);
            }

            if (this.isBase) {
                window.history.replaceState({}, '', localization.url);
            }
        },

        editLocalization(localization) {
            return this.$axios.get(localization.url).then(response => {
                clearTimeout(this.trackDirtyStateTimeout);
                this.trackDirtyState = false;

                const data = response.data;
                this.values = data.values;
                this.originValues = data.originValues;
                this.originMeta = data.originMeta;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.localizedFields = data.localizedFields;
                this.hasOrigin = data.hasOrigin;
                this.publishUrl = data.actions[this.action];
                this.collection = data.collection;
                this.title = data.editing ? data.values.title : this.title;
                this.actions = data.actions;
                this.fieldset = data.blueprint;
                this.isRoot = data.isRoot;
                this.permalink = data.permalink;
                this.site = localization.handle;
                this.localizing = false;

                this.trackDirtyStateTimeout = setTimeout(() => this.trackDirtyState = true, 300); // after any fieldtypes do a debounced update
            })
        },

        createLocalization(localization) {
            this.selectingOrigin = false;

            if (this.isCreating) {
                this.$nextTick(() => window.location = localization.url);
                return;
            }

            const originLocalization = this.localizations.find(e => e.handle === this.selectedOrigin);
            const url = originLocalization.url + '/localize';
            this.$axios.post(url, { site: localization.handle }).then(response => {
                this.editLocalization(response.data).then(() => {
                    this.$events.$emit('localization.created', { store: this.publishContainer });

                    if (this.originValues.published) {
                        this.setFieldValue('published', true);
                    }
                });
            });
        },

        cancelLocalization() {
            this.selectingOrigin = false;
            this.localizing = false;
        },

        localizationStatusText(localization) {
            if (! localization.exists) return 'This entry does not exist for this site.';

            return localization.published
                ? 'This entry exists in this site, and is published.'
                : 'This entry exists in this site, but is not published.';
        },

        openLivePreview() {
            this.sectionsVisible = false;
            this.$wait(200)
                .then(() => {
                    this.isPreviewing = true;
                    return this.$wait(300);
                })
                .then(() => this.sectionsVisible = true);
        },

        closeLivePreview() {
            this.isPreviewing = false;
            this.sectionsVisible = true;
        },

        publishActionCompleted({ published, isWorkingCopy, response }) {
            this.saving = false;
            if (published !== undefined) {
                this.$refs.container.setFieldValue('published', published);
                this.initialPublished = published;
            }
            this.$refs.container.saved();
            this.isWorkingCopy = isWorkingCopy;
            this.confirmingPublish = false;
            this.title = response.data.data.title;
            this.activeLocalization.title = response.data.data.title;
            this.activeLocalization.published = response.data.data.published;
            this.activeLocalization.status = response.data.data.status;
            this.permalink = response.data.data.permalink
            this.$nextTick(() => this.$emit('saved', response));
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

        setAutosaveInterval() {
            const interval = setInterval(() => {
                if (!this.isDirty) return;

                this.isAutosave = true;
                this.save();
            }, this.autosaveInterval);

            this.$store.commit(`publish/${this.publishContainer}/setAutosaveInterval`, interval);
        }
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.quickSave = true;
            this.save();
        });

        this.$store.commit(`publish/${this.publishContainer}/setPreloadedAssets`, this.preloadedAssets);

        if (typeof this.autosaveInterval === 'number') {
            this.setAutosaveInterval();
        }
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));

        this.selectedOrigin = this.originBehavior === 'active'
            ? this.localizations.find(l => l.active)?.handle
            : this.localizations.find(l => l.root)?.handle;
    },

    unmounted() {
        clearTimeout(this.trackDirtyStateTimeout);
    },

    beforeDestroy() {
        this.$store.commit(`publish/${this.publishContainer}/clearAutosaveInterval`);
    },

    destroyed() {
        this.saveKeyBinding.destroy();
        this.quickSaveKeyBinding.destroy();
    }

}
</script>
