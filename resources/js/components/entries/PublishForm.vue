<template>

    <div>
        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a :href="collectionUrl" v-text="collectionTitle" class="text-grey hover:text-blue" />
                </small>
                {{ title }}
            </h1>

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

            <div class="btn flex items-center leading-none py-0 px-sm h-auto justify-between">

                <div
                    class="cursor-pointer p-1 hover:bg-grey-30 rounded flex items-center"
                    v-if="isBase"
                    v-text="__('Live Preview')"
                    @click="openLivePreview" />

                <div
                    class="m-sm cursor-pointer p-1 hover:bg-grey-30 rounded"
                    :class="{ 'opacity-25': !canSave }"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="__('Save')" />

                <div
                    class="cursor-pointer p-1 hover:bg-grey-30 rounded flex items-center"
                    :class="{ 'opacity-25': !canPublish }"
                    :disabled="!canPublish"
                    @click="confirmPublish"
                    v-text="__('Publish')" />

                <div
                    class="cursor-pointer p-1 hover:bg-grey-30 rounded flex items-center"
                    :class="{ 'opacity-25': !canUnpublish }"
                    :disabled="!canUnpublish"
                    @click="confirmUnpublish"
                    v-text="__('Unpublish')" />

                <div
                    class="cursor-pointer p-1 hover:bg-grey-30 rounded flex items-center"
                    :class="{ 'opacity-25': !canCreateRevision }"
                    :disabled="!canCreateRevision"
                    @click="confirmCreateRevision"
                    v-text="__('Create Revision')" />
            </div>

            <confirmation-modal
                v-if="confirmingUnpublish"
                :title="__('Unpublish')"
                :buttonText="__('Unpublish')"
                @confirm="unpublish"
                @cancel="confirmingUnpublish = false"
            >
                <p class="mb-3">{{ __('Are you sure you want to unpublish this entry?') }}</p>
                <text-input v-model="revisionMessage" :placeholder="__('Notes about this revision')" @keydown.enter="unpublish" autofocus />
            </confirmation-modal>

            <confirmation-modal
                v-if="confirmingPublish"
                :title="__('Publish')"
                :buttonText="__('Publish')"
                @confirm="publish"
                @cancel="confirmingPublish = false"
            >
                <p class="mb-3">{{ __('Are you sure you want to publish this entry?') }}</p>
                <text-input v-model="revisionMessage" :placeholder="__('Notes about this revision')" @keydown.enter="publish" autofocus />
            </confirmation-modal>

            <confirmation-modal
                v-if="confirmingCreatingRevision"
                :title="__('Create Revision')"
                :buttonText="__('Create Revision')"
                @confirm="createRevision"
                @cancel="confirmingCreatingRevision = false"
            >
                <p class="mb-3">{{ __('Are you sure you want to create a revision?') }}</p>
                <text-input v-model="revisionMessage" :placeholder="__('Notes about this revision')" @keydown.enter="publish" autofocus />
            </confirmation-modal>

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
import RevisionHistory from '../revision-history/History.vue';

export default {

    components: {
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
            error: null,
            errors: {},
            isPreviewing: false,
            sectionsVisible: true,
            state: 'new',
            revisionMessage: null,
            showRevisionHistory: false,
            published: this.initialPublished,
            confirmingPublish: false,
            confirmingUnpublish: false,
            confirmingCreatingRevision: false,
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

        canUnpublish() {
            return !this.isCreating && !this.canSave && this.published && !this.somethingIsLoading;
        },

        canCreateRevision() {
            return this.canPublish;
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

        stateMsg() {
            if (this.isDirty) {
                return __('Unsaved Changes');
            }

            if (this.state === 'new') {
                return __('New Unsaved Entry')
            } else if (this.state === 'unpublished') {
                return __('Unpublished Entry')
            } else if (this.state === 'published') {
                return __('Published Entry')
            } else if (this.state === 'scheduled') {
                return __('Scheduled Entry')
            } else if (this.state === 'expired') {
                return __('Expired Entry')
            } else {
                return __('Mystery Entry')
            }
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

        confirmUnpublish() {
            if (this.canUnpublish) {
                this.confirmingUnpublish = true;
            }
        },

        confirmCreateRevision() {
            if (this.canCreateRevision) {
                this.confirmingCreatingRevision = true;
            }
        },

        publish() {
            this.saving = true;
            this.confirmingPublish = false;
            this.clearErrors();
            const payload = { message: this.revisionMessage };

            this.$axios.post(this.actions.publish, payload).then(response => {
                this.saving = false;
                this.$notify.success(__('Published'));
                this.$refs.container.saved();
                this.revisionMessage = null;
                this.published = true;
                this.$nextTick(() => this.$emit('saved', response));
            }).catch(e => this.handleAxiosError(e));
        },

        unpublish() {
            this.saving = true;
            this.confirmingUnpublish = false;
            this.clearErrors();
            const payload = { message: this.revisionMessage };

            this.$axios.delete(this.actions.publish, { data: payload }).then(response => {
                this.saving = false;
                this.$notify.success(__('Unpublished'));
                this.$refs.container.saved();
                this.revisionMessage = null;
                this.published = false;
                this.$nextTick(() => this.$emit('saved', response));
            }).catch(e => this.handleAxiosError(e));
        },

        createRevision() {
            this.saving = true;
            this.confirmingCreatingRevision = false;
            this.clearErrors();
            const payload = { message: this.revisionMessage };

            this.$axios.post(this.actions.createRevision, payload).then(response => {
                this.saving = false;
                this.$notify.success(__('Revision created'));
                this.$refs.container.saved();
                this.revisionMessage = null;
                this.$nextTick(() => this.$emit('saved', response));
            }).catch(e => this.handleAxiosError(e));
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
        }

    },

    mounted() {
        this.$mousetrap.bindGlobal(['command+s'], e => {
            e.preventDefault();
            this.save();
        });
    }

}
</script>
