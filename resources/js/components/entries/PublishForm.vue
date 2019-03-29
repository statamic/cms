<template>

    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a :href="collectionUrl" v-text="collectionTitle" class="text-grey hover:text-blue" />
                </small>
                <div class="flex items-center">
                    <span v-if="! isCreating" class="little-dot mr-1 -ml-2" :class="{ 'bg-green-light': published, 'bg-grey-60': !published }" />
                    {{ title }}
                </div>
            </h1>

            <div class="pt-px text-2xs text-grey-60 mr-2" v-if="isDirty" v-text="'Unsaved Changes'" />
            <div class="pt-px text-2xs text-grey-60 mr-2" v-if="isWorkingCopy" v-text="'Unpublished Changes'" />

            <div
                class="mr-3 text-xs flex items-center"
                v-if="localizations.length > 1"
            >
                <button
                    v-for="loc in localizations"
                    :key="loc.handle"
                    class="inline-flex items-center py-1 px-2 rounded outline-none leading-normal"
                    :class="{ 'bg-grey-20': loc.active }"
                    @click="localizationSelected(loc)"
                    v-tooltip.top="localizationStatusText(loc)"
                >
                    <div class="w-4 text-right flex items-center">
                        <loading-graphic :size="14" text="" class="flex -ml-1" v-if="localizing === loc.handle" />
                        <span v-if="localizing != loc.handle" class="little-dot"
                            :class="{
                                'bg-green': loc.published,
                                'bg-grey-40': !loc.published,
                                'bg-red': !loc.exists
                            }" />
                    </div>
                    {{ loc.name }}
                </button>
            </div>

            <button href="" class="btn mr-2 flex items-center" @click="showRevisionHistory = true" v-text="__('History')" />

            <stack name="revision-history" v-if="showRevisionHistory" @closed="showRevisionHistory = false" :narrow="true">
                <revision-history
                    slot-scope="{ close }"
                    :index-url="actions.revisions"
                    :restore-url="actions.restore"
                    @closed="close"
                />
            </stack>

            <button
                class="btn mr-2"
                v-if="isBase"
                v-text="__('Live Preview')"
                @click="openLivePreview" />

            <button
                v-if="!canPublish"
                class="btn btn-primary"
                :class="{ 'opacity-25': !canSave }"
                :disabled="!canSave"
                @click.prevent="save"
                v-text="__('Save')" />

            <button
                v-if="canPublish"
                class="btn btn-primary"
                :class="{ 'opacity-25': !canPublish }"
                :disabled="!canPublish"
                @click="confirmingPublish = true"
                v-text="`${__('Publish')}...`" />

            <publish-actions
                v-if="confirmingPublish"
                :actions="actions"
                :published="published"
                @closed="confirmingPublish = false"
                @saving="saving = true"
                @saved="publishActionCompleted"
            />

            <slot name="action-buttons-right" />
        </div>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :fieldset="fieldset"
            :values="values"
            :meta="meta"
            :errors="errors"
            :site="site"
            @updated="values = $event"
        >
            <live-preview
                slot-scope="{}"
                :name="publishContainer"
                :url="livePreviewUrl"
                :previewing="isPreviewing"
                :values="values"
                :blueprint="fieldset.handle"
                :amp="amp"
                @opened-via-keyboard="openLivePreview"
                @closed="closeLivePreview"
            >
                <transition name="live-preview-sections-drop">
                    <publish-sections v-show="sectionsVisible" :live-preview="isPreviewing" />
                </transition>
            </live-preview>
        </publish-container>
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
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialLocalizations: Array,
        initialSite: String,
        initialIsWorkingCopy: Boolean,
        collectionTitle: String,
        collectionUrl: String,
        initialActions: Object,
        method: String,
        amp: Boolean,
        initialPublished: Boolean,
        isCreating: Boolean,
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
            return this.isDirty && !this.somethingIsLoading;
        },

        canPublish() {
            return !this.isCreating && !this.canSave && !this.somethingIsLoading;
        },

        livePreviewUrl() {
            return _.findWhere(this.localizations, { active: true }).url + '/preview';
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
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
                blueprint: this.fieldset.handle
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
            this.$axios.get(localization.url).then(response => {
                const data = response.data;
                this.values = data.values;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.publishUrl = data.actions[this.action];
                this.collection = data.collection;
                this.title = data.editing ? data.values.title : this.title;
                this.actions = data.actions;
                this.fieldset = data.blueprint;
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.removeNavigationWarning());
            })
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
        }

    },

    mounted() {
        this.$mousetrap.bindGlobal(['command+s'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.canPublish ? this.confirmPublish() : this.save();
        });
    }

}
</script>
