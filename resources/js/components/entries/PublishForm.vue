<template>
    <div>
        <Header>
            <template #title>
                <StatusIndicator v-if="!isCreating" :status="activeLocalization.status" />
                {{ formattedTitle }}
            </template>

            <ItemActions
                v-if="!isCreating && hasItemActions"
                :item="values.id"
                :url="itemActionUrl"
                :actions="itemActions"
                :is-dirty="isDirty"
                @started="actionStarted"
                @completed="actionCompleted"
                v-slot="{ actions: itemActions }"
            >
                <Dropdown v-if="canEditBlueprint || hasItemActions">
                    <template #trigger>
                        <Button icon="ui/dots" variant="ghost" :aria-label="__('Open dropdown menu')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" v-if="canEditBlueprint" :href="actions.editBlueprint" />
                        <DropdownSeparator v-if="canEditBlueprint && itemActions.length" />
                        <DropdownItem
                            v-for="action in itemActions"
                            :key="action.handle"
                            :text="__(action.title)"
                            :icon="action.icon"
                            :variant="action.dangerous ? 'destructive' : 'default'"
                            @click="action.run"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ItemActions>

            <ui-badge icon="padlock-locked" :text="__('Read Only')" variant="flat" v-if="readOnly" />

            <div class="flex items-center gap-3">
                <save-button-options
                    v-if="!readOnly"
                    :show-options="!revisionsEnabled && !isInline"
                    :preferences-prefix="preferencesPrefix"
                >
                    <Button
                        :disabled="!canSave"
                        :variant="!revisionsEnabled ? 'primary' : 'default'"
                        @click.prevent="save"
                        v-text="saveText"
                    />
                </save-button-options>

                <save-button-options
                    v-if="revisionsEnabled && !isCreating"
                    :show-options="!isInline"
                    :preferences-prefix="preferencesPrefix"
                >
                    <Button
                        variant="primary"
                        :disabled="!canPublish"
                        @click="confirmingPublish = true"
                        :text="publishButtonText"
                    />
                </save-button-options>
            </div>

            <slot name="action-buttons-right" />
        </Header>

        <PublishContainer
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :reference="initialReference"
            :blueprint="fieldset"
            v-model="values"
            :extra-values="extraValues"
            :meta="meta"
            :origin-values="originValues"
            :origin-meta="originMeta"
            :errors="errors"
            :site="site"
            v-model:modified-fields="localizedFields"
            :track-dirty-state="trackDirtyState"
            :sync-field-confirmation-text="syncFieldConfirmationText"
        >
            <LivePreview
                :enabled="isPreviewing"
                :url="livePreviewUrl"
                :targets="previewTargets"
                @opened="openLivePreview"
                @closed="closeLivePreview"
            >
                <PublishComponents />

                <PublishTabs>
                    <template #actions>
                        <div class="space-y-6">
                            <!-- Live Preview / Visit URL Buttons -->
                            <div v-if="collectionHasRoutes">
                                <div class="flex flex-wrap gap-4" v-if="showLivePreviewButton || showVisitUrlButton">
                                    <Button
                                        :text="__('Live Preview')"
                                        class="flex-1"
                                        icon="live-preview"
                                        @click="openLivePreview"
                                        v-if="showLivePreviewButton"
                                    />
                                    <Button
                                        :href="permalink"
                                        :text="__('Visit URL')"
                                        class="flex-1"
                                        icon="external-link"
                                        target="_blank"
                                        v-if="showVisitUrlButton"
                                    />
                                </div>
                            </div>

                            <!-- Published Switch -->
                            <Panel class="flex justify-between px-5 py-3" v-if="!revisionsEnabled">
                                <Heading :text="__('Published')" />
                                <Switch
                                    :model-value="published"
                                    :read-only="!canManagePublishState"
                                    @update:model-value="setFieldValue('published', $event)"
                                />
                            </Panel>

                            <!-- Revisions -->
                            <Panel v-if="revisionsEnabled && !isCreating">
                                <PanelHeader class="flex items-center justify-between">
                                    <Heading :text="__('Revisions')" />
                                    <Button
                                        @click="showRevisionHistory = true"
                                        icon="history"
                                        :text="__('View History')"
                                        size="xs"
                                        class="-me-4"
                                    />
                                </PanelHeader>
                                <Card class="space-y-2">
                                    <Subheading v-if="published" class="flex items-center gap-2">
                                        <Icon name="checkmark" class="text-green-600" />
                                        {{ __('Entry has a published version') }}
                                    </Subheading>
                                    <Subheading v-else class="flex items-center gap-2 text-yellow-600">
                                        <Icon name="warning-diamond" />
                                        {{ __('Entry has not been published') }}
                                    </Subheading>
                                    <Subheading v-if="!isWorkingCopy && published" class="flex items-center gap-2">
                                        <Icon name="checkmark" class="text-green-600" />
                                        {{ __('This is the published version') }}
                                    </Subheading>
                                    <Subheading v-if="isDirty" class="flex items-center gap-2 text-yellow-600">
                                        <Icon name="warning-diamond" />
                                        {{ __('Unsaved changes') }}
                                    </Subheading>
                                </Card>
                            </Panel>

                            <LocalizationsCard
                                v-if="showLocalizationSelector"
                                :localizations
                                :localizing="localizing !== null"
                                @selected="localizationSelected"
                            />
                        </div>
                    </template>
                </PublishTabs>
                <template #buttons>
                    <Button
                        v-if="!readOnly"
                        size="sm"
                        :variant="revisionsEnabled ? 'default' : 'primary'"
                        :disabled="!canSave"
                        @click.prevent="save"
                        :text="saveText"
                    ></Button>

                    <Button
                        v-if="revisionsEnabled"
                        size="sm"
                        variant="primary"
                        :disabled="!canPublish"
                        @click="confirmingPublish = true"
                        :text="publishButtonText"
                    />
                </template>
            </LivePreview>
        </PublishContainer>

        <stack
            name="revision-history"
            v-if="showRevisionHistory"
            @closed="showRevisionHistory = false"
            :narrow="true"
            v-slot="{ close }"
        >
            <revision-history
                :index-url="actions.revisions"
                :restore-url="actions.restore"
                :reference="initialReference"
                :can-restore-revisions="!readOnly"
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
            @failed="publishActionFailed"
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
                    <ui-description class="mt-2" :text="__('messages.entry_origin_instructions')" />
                    <Select class="w-full" v-model="selectedOrigin" :options="originOptions" placeholder="" />
                </div>
            </div>
        </confirmation-modal>

        <confirmation-modal
            v-if="pendingLocalization"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Continue')"
            :danger="true"
            @confirm="confirmSwitchLocalization"
            @cancel="pendingLocalization = null"
        />

        <confirmation-modal
            v-if="syncingField"
            :title="__('Sync Field')"
            :body-text="__('Are you sure? This field\'s value will be replaced by the value in the original entry.')"
            :button-text="__('Sync Field')"
            :danger="true"
            @confirm="confirmSyncField"
            @cancel="syncingField = null"
        />
    </div>
</template>

<script>
import ItemActions from '../../components/actions/ItemActions.vue';
import PublishActions from './PublishActions.vue';
import SaveButtonOptions from '../publish/SaveButtonOptions.vue';
import RevisionHistory from '../revision-history/History.vue';
import HasPreferences from '../data-list/HasPreferences';
import HasActions from '../publish/HasActions';
import striptags from 'striptags';
import clone from '@statamic/util/clone.js';
import {
    Button,
    Card,
    CardPanel,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    DropdownSeparator,
    Header,
    Heading,
    Icon,
    Panel,
    PanelHeader,
    StatusIndicator,
    Subheading,
    Switch,
    Select,
} from '@statamic/ui';
import PublishContainer from '@statamic/components/ui/Publish/Container.vue';
import PublishTabs from '@statamic/components/ui/Publish/Tabs.vue';
import PublishComponents from '@statamic/components/ui/Publish/Components.vue';
import LocalizationsCard from '@statamic/components/ui/Publish/Localizations.vue';
import LivePreview from '@statamic/components/ui/LivePreview/LivePreview.vue';
import resetValuesFromResponse from '@statamic/util/resetValuesFromResponse.js';
import { SavePipeline } from '@statamic/exports.js';
import { computed, ref } from 'vue';
const { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks, PipelineStopped } = SavePipeline;

let saving = ref(false);
let errors = ref({});
let container = null;

export default {
    mixins: [HasPreferences, HasActions],

    components: {
        Button,
        Card,
        CardPanel,
        Dropdown,
        DropdownItem,
        DropdownMenu,
        DropdownSeparator,
        Header,
        Heading,
        Icon,
        ItemActions,
        LivePreview,
        LocalizationsCard,
        Panel,
        PanelHeader,
        PublishActions,
        PublishComponents,
        PublishContainer,
        PublishTabs,
        RevisionHistory,
        SaveButtonOptions,
        StatusIndicator,
        Subheading,
        Switch,
        Select,
    },

    props: {
        publishContainer: String,
        initialReference: String,
        initialFieldset: Object,
        initialValues: Object,
        initialExtraValues: Object,
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
        initialActions: Object,
        method: String,
        isCreating: Boolean,
        isInline: Boolean,
        initialReadOnly: Boolean,
        initialPermalink: String,
        revisionsEnabled: Boolean,
        canEditBlueprint: Boolean,
        canManagePublishState: Boolean,
        createAnotherUrl: String,
        initialListingUrl: String,
        collectionHasRoutes: Boolean,
        previewTargets: Array,
        autosaveInterval: Number,
        parent: String,
    },

    data() {
        return {
            actions: this.initialActions,
            localizing: false,
            trackDirtyState: true,
            fieldset: this.initialFieldset,
            title: this.initialTitle,
            values: clone(this.initialValues),
            visibleValues: {},
            meta: clone(this.initialMeta),
            extraValues: clone(this.initialExtraValues),
            localizations: clone(this.initialLocalizations),
            localizedFields: this.initialLocalizedFields,
            hasOrigin: this.initialHasOrigin,
            originValues: this.initialOriginValues,
            originMeta: this.initialOriginMeta,
            site: this.initialSite,
            selectingOrigin: false,
            selectedOrigin: null,
            isWorkingCopy: this.initialIsWorkingCopy,
            isPreviewing: false,
            tabsVisible: true,
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
            permalink: this.initialPermalink,

            saveKeyBinding: null,
            quickSaveKeyBinding: null,
            quickSave: false,
            isAutosave: false,
            autosaveIntervalInstance: null,
            syncFieldConfirmationText: __('messages.sync_entry_field_confirmation_text'),
            pendingLocalization: null,
            syncingField: null,
        };
    },

    computed: {
        saving() {
            return saving.value;
        },

        errors() {
            return errors.value;
        },

        formattedTitle() {
            return striptags(__(this.title));
        },

        somethingIsLoading() {
            return !this.$progress.isComplete();
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

        listingUrl() {
            return `${this.initialListingUrl}?site=${this.site}`;
        },

        livePreviewUrl() {
            return this.localizations.find((l) => l.active).livePreviewUrl;
        },

        showLivePreviewButton() {
            return !this.isCreating && this.isBase && this.livePreviewUrl;
        },

        showVisitUrlButton() {
            return !!this.permalink;
        },

        showLocalizationSelector() {
            return this.localizations.length > 1;
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

        activeLocalization() {
            return this.localizations.find((l) => l.active);
        },

        saveText() {
            switch (true) {
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

        publishButtonText() {
            if (this.canManagePublishState) {
                return `${__('Publish')}…`;
            }

            return `${__('Create Revision')}…`;
        },

        isUnpublishing() {
            return this.initialPublished && !this.published && !this.isCreating;
        },

        isDraft() {
            return !this.published;
        },

        afterSaveOption() {
            return this.getPreference('after_save');
        },

        originOptions() {
            return this.localizations
                .filter((localization) => localization.exists)
                .map((localization) => ({
                    value: localization.handle,
                    label: localization.name,
                }));
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    watch: {
        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-entry-publish-form`, saving);
        },

        title(title) {
            if (this.isBase) {
                const arrow = this.direction === 'ltr' ? '‹' : '›';
                const parts = document.title.split(arrow);

                document.title = `${title} ${arrow} ${parts[1]?.trim()}`;
            }
        },
    },

    methods: {
        save() {
            if (!this.canSave) {
                this.quickSave = false;
                return;
            }

            new Pipeline()
                .provide({ container, errors, saving })
                .through([
                    new BeforeSaveHooks('entry', {
                        collection: this.collectionHandle,
                        values: this.values,
                    }),
                    new Request(this.actions.save, this.method, {
                        _blueprint: this.fieldset.handle,
                        _localized: this.localizedFields,
                        _parent: this.parent,
                    }),
                    new AfterSaveHooks('entry', {
                        collection: this.collectionHandle,
                        reference: this.initialReference,
                    }),
                ])
                .then((response) => {
                    // If revisions are enabled, just emit event.
                    if (this.revisionsEnabled) {
                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.values = resetValuesFromResponse(response.data.data.values, this.$refs.container);
                        this.extraValues = response.data.data.extraValues;
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500);
                        this.$nextTick(() => this.$emit('saved', response));
                        return;
                    }

                    this.title = response.data.data.title;
                    this.isWorkingCopy = true;
                    if (!this.revisionsEnabled) this.permalink = response.data.data.permalink;
                    if (!this.isCreating && !this.isAutosave) this.$toast.success(__('Saved'));

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
                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500);
                        this.initialPublished = response.data.data.published;
                        this.activeLocalization.published = response.data.data.published;
                        this.activeLocalization.status = response.data.data.status;
                        this.$nextTick(() => this.$emit('saved', response));
                    }

                    this.quickSave = false;
                    this.isAutosave = false;
                })
                .catch((e) => {
                    if (!(e instanceof PipelineStopped)) {
                        this.$toast.error(__('Something went wrong'));
                        console.error(e);
                    }
                });
        },

        confirmPublish() {
            if (this.canPublish) {
                this.confirmingPublish = true;
            }
        },

        localizationSelected(localization) {
            if (!this.canSave) {
                if (localization.exists) this.editLocalization(localization);
                return;
            }

            if (localization.active) return;

            if (this.isDirty) {
                this.pendingLocalization = localization;
                return;
            }

            this.switchToLocalization(localization);
        },

        confirmSwitchLocalization() {
            this.switchToLocalization(this.pendingLocalization);
            this.pendingLocalization = null;
        },

        switchToLocalization(localization) {
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
            return this.$axios.get(localization.url).then((response) => {
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
                this.permalink = data.permalink;
                this.site = localization.handle;
                this.localizing = false;
                this.initialPublished = data.values.published;
                this.readOnly = data.readOnly;

                this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500); // after any fieldtypes do a debounced update
            });
        },

        createLocalization(localization) {
            this.selectingOrigin = false;

            if (this.isCreating) {
                this.$nextTick(() => (window.location = localization.url));
                return;
            }

            const originLocalization = this.localizations.find((e) => e.handle === this.selectedOrigin);
            const url = originLocalization.url + '/localize';
            this.$axios.post(url, { site: localization.handle }).then((response) => {
                this.editLocalization(response.data).then(() => {
                    this.$events.$emit('localization.created', { container: this.$refs.container });

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
            if (!localization.exists) return 'This entry does not exist for this site.';

            return localization.published
                ? 'This entry exists in this site, and is published.'
                : 'This entry exists in this site, but is not published.';
        },

        openLivePreview() {
            this.tabsVisible = false;
            this.$wait(200)
                .then(() => {
                    this.isPreviewing = true;
                    return this.$wait(300);
                })
                .then(() => (this.tabsVisible = true));
        },

        closeLivePreview() {
            this.isPreviewing = false;
            this.tabsVisible = true;
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
                this.title = response.data.data.title;
                clearTimeout(this.trackDirtyStateTimeout);
                this.trackDirtyState = false;
                this.values = resetValuesFromResponse(response.data.data.values, this.$refs.container);
                this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500);
                this.activeLocalization.title = response.data.data.title;
                this.activeLocalization.published = response.data.data.published;
                this.activeLocalization.status = response.data.data.status;
                this.permalink = response.data.data.permalink;
                this.$nextTick(() => this.$emit('saved', response));
            }
        },

        publishActionFailed() {
            this.confirmPublish = false;
            this.saving = false;
        },

        setFieldValue(handle, value) {
            if (this.hasOrigin) this.desyncField(handle);

            this.$refs.container.setFieldValue(handle, value);
        },

        syncField(handle) {
            this.syncingField = handle;
        },

        confirmSyncField() {
            const handle = this.syncingField;
            this.localizedFields = this.localizedFields.filter((field) => field !== handle);
            this.$refs.container.setFieldValue(handle, this.originValues[handle]);

            // Update the meta for this field. For instance, a relationship field would have its data preloaded into it.
            // If you sync the field, the preloaded data would be outdated and an ID would show instead of the titles.
            this.meta[handle] = this.originMeta[handle];
            this.syncingField = null;
        },

        desyncField(handle) {
            if (!this.localizedFields.includes(handle)) this.localizedFields.push(handle);

            this.$refs.container?.dirty();
        },

        setAutosaveInterval() {
            this.autosaveIntervalInstance = setInterval(() => {
                if (!this.isDirty) return;

                this.isAutosave = true;
                this.save();
            }, this.autosaveInterval);
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                if (!this.revisionsEnabled) this.permalink = response.data.permalink;
                clearTimeout(this.trackDirtyStateTimeout);
                this.trackDirtyState = false;
                this.values = resetValuesFromResponse(response.data.values, this.$refs.container);
                this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 500);
                this.initialPublished = response.data.published;
                this.activeLocalization.published = response.data.published;
                this.activeLocalization.status = response.data.status;
                this.itemActions = response.data.itemActions;
            }
        },
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], (e) => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.quickSave = true;
            this.save();
        });

        if (typeof this.autosaveInterval === 'number') {
            this.setAutosaveInterval();
        }
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));

        this.selectedOrigin =
            this.originBehavior === 'active'
                ? this.localizations.find((l) => l.active)?.handle
                : this.localizations.find((l) => l.root)?.handle;

        container = computed(() => this.$refs.container);
    },

    unmounted() {
        clearTimeout(this.trackDirtyStateTimeout);
    },

    beforeUnmount() {
        if (this.autosaveIntervalInstance) clearInterval(this.autosaveIntervalInstance);
    },

    unmounted() {
        this.saveKeyBinding.destroy();
        this.quickSaveKeyBinding.destroy();
    },
};
</script>
