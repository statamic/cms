<template>

    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a :href="collectionUrl" v-text="collectionTitle" class="text-grey hover:text-blue" />
                </small>
                <div class="flex items-center">
                    <span v-if="! isCreating"
                        class="little-dot mr-1"
                        :class="{ 'bg-green-light': published, 'bg-grey-60': !published }" />
                    {{ title }}
                </div>
            </h1>

            <div class="pt-px text-2xs text-grey-60 flex" v-if="readOnly">
                <svg-icon name="lock" class="w-4 mr-sm -mt-sm" /> {{ __('Read Only') }}
            </div>
            <div class="pt-px text-2xs text-grey-60" v-if="isWorkingCopy" v-text="'Unpublished Changes'" />
            <div class="pt-px text-2xs text-grey-60" v-else-if="isDirty" v-text="'Unsaved Changes'" />
            <div class="pt-px text-2xs text-grey-60" v-else-if="!published" v-text="'Unpublished Entry'" />

            <slot name="action-buttons-right" />
        </div>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :fieldset="fieldset"
            :values="values"
            :reference="initialReference"
            :meta="meta"
            :errors="errors"
            :site="site"
            :localized-fields="localizedFields"
            :is-root="isRoot"
            @updated="values = $event"
        >
            <live-preview
                slot-scope="{ container, components }"
                :name="publishContainer"
                :url="livePreviewUrl"
                :previewing="isPreviewing"
                :values="values"
                :blueprint="fieldset.handle"
                :amp="amp"
                @opened-via-keyboard="openLivePreview"
                @closed="closeLivePreview"
            >
                <div>
                    <component
                        v-for="component in components"
                        :key="component.name"
                        :is="component.name"
                        :container="container"
                        v-bind="component.props"
                    />

                    <transition name="live-preview-sections-drop">
                        <publish-sections
                            v-show="sectionsVisible"
                            :read-only="readOnly"
                            :syncable="hasOrigin"
                            @updated="setValue"
                            @synced="syncField"
                            @desynced="desyncField"
                            @focus="container.$emit('focus', $event)"
                            @blur="container.$emit('blur', $event)"
                        >
                            <template #actions="{ shouldShowSidebar }">

                                <div class="p-2" :class="{ 'flex justify-between items-center p-1 card': !shouldShowSidebar }">

                                    <div :class="{ 'mb-2': shouldShowSidebar, 'min-w-xs': !shouldShowSidebar }">
                                        <button
                                            v-if="!readOnly && !canPublish"
                                            class="btn btn-primary w-full"
                                            :class="{ 'opacity-25': !canSave }"
                                            :disabled="!canSave"
                                            @click.prevent="save"
                                            v-text="__('Save')" />

                                        <button
                                            v-if="canPublish"
                                            class="btn btn-primary w-full"
                                            :class="{ 'opacity-25': !canPublish }"
                                            :disabled="!canPublish"
                                            @click="confirmingPublish = true"
                                            v-text="`${__('Publish')}...`" />
                                    </div>

                                    <div class="flex flex-wrap justify-center text-grey text-2xs">
                                        <button
                                            v-if="!revisionsEnabled"
                                            class="flex items-center m-1 whitespace-no-wrap outline-none"
                                            :class="{ 'text-green': published }"
                                            @click="togglePublishState"
                                        >
                                            <span class="little-dot mr-sm" :class="{ 'bg-green': published, 'bg-grey-60': !published }" />
                                            <span v-text="published ? __('Published') : __('Draft')" />
                                        </button>

                                        <button
                                            class="flex items-center m-1 whitespace-no-wrap"
                                            v-if="!isCreating && revisionsEnabled"
                                            @click="showRevisionHistory = true">
                                            <svg-icon name="time" class="w-4 mr-sm" /> {{ __('History') }}
                                        </button>

                                        <button
                                            class="flex items-center m-1 whitespace-no-wrap"
                                            v-if="isBase"
                                            @click="openLivePreview">
                                            <svg-icon name="search" class="w-4 mr-sm" /> {{ __('Preview') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="" v-if="localizations.length > 1">
                                    <div
                                        v-for="option in localizations"
                                        :key="option.handle"
                                        class="revision-item flex items-center border-grey-30"
                                        :class="{ 'opacity-50': !option.active }"
                                        @click="localizationSelected(option)"
                                    >
                                        <div class="flex-1 flex items-center">
                                            <span class="little-dot mr-1" :class="{
                                                'bg-green': option.published,
                                                'bg-grey-50': !option.published,
                                                'bg-red': !option.exists
                                            }" />
                                            {{ option.name }}
                                            <loading-graphic :size="14" text="" class="ml-1" v-if="localizing === option.handle" />
                                        </div>
                                        <div class="badge bg-orange" v-if="option.origin" v-text="__('Origin')" />
                                        <div class="badge bg-blue" v-if="option.active" v-text="__('Active')" />
                                        <div class="badge bg-purple" v-if="option.root && !option.origin && !option.active" v-text="__('Root')" />
                                    </div>
                                </div>

                            </template>
                        </publish-sections>
                    </transition>
                </div>
            </live-preview>
        </publish-container>

        <stack name="revision-history" v-if="showRevisionHistory" @closed="showRevisionHistory = false" :narrow="true">
            <revision-history
                slot-scope="{ close }"
                :index-url="actions.revisions"
                :restore-url="actions.restore"
                @closed="close"
            />
        </stack>

        <publish-actions
            v-if="confirmingPublish"
            :actions="actions"
            :published="published"
            @closed="confirmingPublish = false"
            @saving="saving = true"
            @saved="publishActionCompleted"
        />
    </div>

</template>


<script>
import PublishActions from './PublishActions.vue';
import RevisionHistory from '../revision-history/History.vue';

export default {

    components: {
        PublishActions,
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
        initialHasOrigin: Boolean,
        initialOriginValues: Object,
        initialOriginMeta: Object,
        initialSite: String,
        initialIsWorkingCopy: Boolean,
        collectionTitle: String,
        collectionUrl: String,
        initialActions: Object,
        method: String,
        amp: Boolean,
        initialPublished: Boolean,
        isCreating: Boolean,
        initialReadOnly: Boolean,
        initialIsRoot: Boolean,
        revisionsEnabled: Boolean
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
            originValues: this.initialOriginValues,
            originMeta: this.initialOriginMeta,
            site: this.initialSite,
            isWorkingCopy: this.initialIsWorkingCopy,
            error: null,
            errors: {},
            isPreviewing: false,
            sectionsVisible: true,
            state: 'new',
            revisionMessage: null,
            showRevisionHistory: false,
            published: this.initialPublished,
            confirmingPublish: false,
            readOnly: this.initialReadOnly,
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
            return !this.readOnly && this.isDirty && !this.somethingIsLoading;
        },

        canPublish() {
            if (!this.revisionsEnabled) return false;

            return !this.readOnly && !this.isCreating && !this.canSave && !this.somethingIsLoading;
        },

        livePreviewUrl() {
            return _.findWhere(this.localizations, { active: true }).url + '/preview';
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

        activeLocalization() {
            return _.findWhere(this.localizations, { active: true });
        }

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
            if (!this.canSave) return;

            this.saving = true;
            this.clearErrors();

            const payload = { ...this.values, ...{
                blueprint: this.fieldset.handle,
                published: this.published,
                _localized: this.localizedFields,
            }};

            this.$axios[this.method](this.actions.save, payload).then(response => {
                this.saving = false;
                this.title = response.data.title;
                this.isWorkingCopy = true;
                if (!this.isCreating) this.$notify.success('Saved');
                this.$refs.container.saved();
                this.$nextTick(() => this.$emit('saved', response));
            }).catch(e => this.handleAxiosError(e));
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
                this.$notify.error(message);
            } else {
                this.$notify.error('Something went wrong');
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

            if (this.publishContainer === 'base') {
                window.history.replaceState({}, '', localization.url);
            }
        },

        editLocalization(localization) {
            this.$axios.get(localization.url).then(response => {
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
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.removeNavigationWarning());
            })
        },

        createLocalization(localization) {
            const url = this.activeLocalization.url + '/localize';
            this.$axios.post(url, { site: localization.handle }).then(response => {
                this.editLocalization(response.data);
            });
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
            this.$refs.container.saved();
            if (published !== undefined) this.published = published;
            this.isWorkingCopy = isWorkingCopy;
            this.confirmingPublish = false;
            this.$nextTick(() => this.$emit('saved', response));
        },

        setValue(handle, value) {
            if (this.hasOrigin) this.desyncField(handle);

            this.$refs.container.setValue(handle, value);
        },

        syncField(handle) {
            if (! confirm('Are you sure? This field\'s value will be replaced by the value in the original entry.'))
                return;

            this.localizedFields = this.localizedFields.filter(field => field !== handle);
            this.$refs.container.setValue(handle, this.originValues[handle]);

            // Update the meta for this field. For instance, a relationship field would have its data preloaded into it.
            // If you sync the field, the preloaded data would be outdated and an ID would show instead of the titles.
            this.meta[handle] = this.originMeta[handle];
        },

        desyncField(handle) {
            if (!this.localizedFields.includes(handle))
                this.localizedFields.push(handle);

            this.$refs.container.dirty();
        },

        togglePublishState() {
            this.published = !this.published;
            this.$refs.container.dirty();
        }

    },

    mounted() {
        this.$mousetrap.bindGlobal(['command+s'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.canPublish ? this.confirmPublish() : this.save();
        });
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));
    }

}
</script>
