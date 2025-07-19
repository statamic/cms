<template>
    <div>
        <Header>
            <template #title>
                <span
                    v-if="!isCreating"
                    class="little-dot -top-1"
                    :class="activeLocalization.published ? 'published' : 'draft'"
                    v-tooltip="__(activeLocalization.status)"
                />
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
                <Dropdown class="ltr:mr-4 rtl:ml-4" v-if="canEditBlueprint || hasItemActions">
                    <template #trigger>
                        <Button icon="ui/dots" variant="ghost" :aria-label="__('Open dropdown menu')" />
                    </template>
                    <DropdownMenu>
                        <DropdownItem
                            :text="__('Edit Blueprint')"
                            icon="blueprint-edit"
                            v-if="canEditBlueprint"
                            :redirect="actions.editBlueprint"
                        />
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

            <div class="text-2xs flex pt-px text-gray-600 ltr:mr-4 rtl:ml-4" v-if="readOnly">
                <svg-icon name="light/lock" class="-mt-1 w-4 ltr:mr-1 rtl:ml-1" /> {{ __('Read Only') }}
            </div>

            <div class="hidden items-center md:flex">
                <save-button-options v-if="!readOnly" :show-options="!isInline" :preferences-prefix="preferencesPrefix">
                    <Button :disabled="!canSave" variant="primary" @click.prevent="save" :text="saveText" />
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
            :meta="meta"
            :origin-values="originValues"
            :origin-meta="originMeta"
            :errors="errors"
            :site="site"
            :localized-fields="localizedFields"
            :sync-field-confirmation-text="syncFieldConfirmationText"
        >
            <LivePreview
                :enabled="isPreviewing"
                :targets="previewTargets"
                :url="livePreviewUrl"
                @opened="openLivePreview"
                @closed="closeLivePreview"
            >
                <PublishComponents />

                <PublishTabs>
                    <template #actions>
                        <div
                            class="space-y-6"
                            v-if="showLivePreviewButton || showVisitUrlButton || showLocalizationSelector"
                        >
                            <div class="flex flex-wrap gap-4" v-if="showLivePreviewButton || showVisitUrlButton">
                                <Button
                                    :text="__('Live Preview')"
                                    class="flex-1"
                                    icon="live-preview"
                                    v-if="showLivePreviewButton"
                                    @click="openLivePreview"
                                />
                                <Button
                                    :href="permalink"
                                    :text="__('Visit URL')"
                                    class="flex-1"
                                    icon="external-link"
                                    v-if="showVisitUrlButton"
                                    target="_blank"
                                />
                            </div>

                            <LocalizationsCard
                                v-if="showLocalizationSelector"
                                :localizations
                                :localizing
                                @selected="localizationSelected"
                            />
                        </div>
                    </template>
                </PublishTabs>
            </LivePreview>
        </PublishContainer>
    </div>
</template>

<script>
import SaveButtonOptions from '../publish/SaveButtonOptions.vue';
import HasPreferences from '../data-list/HasPreferences';
import HasActions from '../publish/HasActions';
import striptags from 'striptags';
import clone from '@statamic/util/clone.js';
import {
    Header,
    Badge,
    Button,
    CardPanel,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    DropdownSeparator,
} from '@statamic/ui';
import PublishContainer from '@statamic/components/ui/Publish/Container.vue';
import PublishTabs from '@statamic/components/ui/Publish/Tabs.vue';
import PublishComponents from '@statamic/components/ui/Publish/Components.vue';
import LivePreview from '@statamic/components/ui/LivePreview/LivePreview.vue';
import { SavePipeline } from '@statamic/exports.js';
import { ref, computed } from 'vue';
const { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks, PipelineStopped } = SavePipeline;
import LocalizationsCard from '@statamic/components/ui/Publish/Localizations.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

let saving = ref(false);
let errors = ref({});
let container = null;

export default {
    mixins: [HasPreferences, HasActions],

    components: {
        ItemActions,
        Header,
        Badge,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        DropdownSeparator,
        SaveButtonOptions,
        PublishContainer,
        PublishTabs,
        PublishComponents,
        LivePreview,
        Button,
        CardPanel,
        LocalizationsCard,
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
        taxonomyHandle: String,
        initialActions: Object,
        method: String,
        initialPublished: Boolean,
        isCreating: Boolean,
        isInline: Boolean,
        initialReadOnly: Boolean,
        initialPermalink: String,
        canEditBlueprint: Boolean,
        createAnotherUrl: String,
        listingUrl: String,
        previewTargets: Array,
        hasTemplate: Boolean,
    },

    data() {
        return {
            actions: this.initialActions,
            localizing: false,
            fieldset: this.initialFieldset,
            title: this.initialTitle,
            values: clone(this.initialValues),
            visibleValues: {},
            meta: clone(this.initialMeta),
            localizations: clone(this.initialLocalizations),
            localizedFields: this.initialLocalizedFields,
            hasOrigin: this.initialHasOrigin,
            originValues: this.initialOriginValues || {},
            originMeta: this.initialOriginMeta || {},
            site: this.initialSite,
            isPreviewing: false,
            state: 'new',
            published: this.initialPublished,
            readOnly: this.initialReadOnly,
            permalink: this.initialPermalink,
            preferencesPrefix: `taxonomies.${this.taxonomyHandle}`,
            saveKeyBinding: null,
            quickSaveKeyBinding: null,
            quickSave: false,
            syncFieldConfirmationText: __('messages.sync_term_field_confirmation_text'),
        };
    },

    computed: {
        saving() {
            return saving.value;
        },

        errors() {
            return errors.value;
        },

        store() {
            return this.$refs.container.store;
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

        livePreviewUrl() {
            return this.localizations.find((l) => l.active).livePreviewUrl;
        },

        showLivePreviewButton() {
            return !this.isCreating && this.isBase && this.livePreviewUrl && this.showVisitUrlButton;
        },

        showVisitUrlButton() {
            return !!this.permalink && this.hasTemplate;
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
            if (this.published) return __('Save & Publish');

            if (!this.published && this.initialPublished) return __('Save & Unpublish');

            return __('Save');
        },

        afterSaveOption() {
            return this.getPreference('after_save');
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },
    },

    watch: {
        published(published) {
            this.$refs.container.dirty();
        },

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
                        taxonomy: this.taxonomyHandle,
                        values: this.values,
                        container: this.$refs.container,
                        storeName: this.publishContainer,
                    }),
                    new Request(this.actions.save, this.method, {
                        _blueprint: this.fieldset.handle,
                        published: this.published,
                        _localized: this.localizedFields,
                    }),
                    new AfterSaveHooks('entry', {
                        taxonomy: this.taxonomyHandle,
                        reference: this.initialReference,
                    }),
                ])
                .then((response) => {
                    this.title = response.data.data.title;
                    this.permalink = response.data.data.permalink;
                    if (!this.isCreating) this.$toast.success(__('Saved'));

                    let nextAction = this.quickSave ? 'continue_editing' : this.afterSaveOption;

                    // If the user has opted to create another entry, redirect them to create page.
                    if (!this.isInline && this.afterSaveOption === 'create_another') {
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
                        this.$nextTick(() => this.$emit('saved', response));
                    }

                    this.quickSave = false;
                })
                .catch((e) => {
                    if (!(e instanceof PipelineStopped)) {
                        this.$toast.error(__('Something went wrong'));
                        console.error(e);
                    }
                });
        },

        localizationSelected(localization) {
            if (localization.active) return;

            if (this.isDirty) {
                if (!confirm(__('Are you sure? Unsaved changes will be lost.'))) {
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
            this.$axios.get(localization.url).then((response) => {
                const data = response.data;
                this.values = data.values;
                this.originValues = data.originValues;
                this.originMeta = data.originMeta;
                this.meta = data.meta;
                this.localizations = data.localizations;
                this.localizedFields = data.localizedFields;
                this.hasOrigin = data.hasOrigin;
                this.taxonomy = data.taxonomy;
                this.title = data.editing ? data.values.title : this.title;
                this.actions = data.actions;
                this.fieldset = data.blueprint;
                this.site = localization.handle;
                this.localizing = false;
                this.$nextTick(() => this.$refs.container.clearDirtyState());
            });
        },

        createLocalization(localization) {
            const url = this.activeLocalization.url + '/localize';
            this.$axios.post(url, { site: localization.handle }).then((response) => {
                this.editLocalization(response.data);
            });
        },

        openLivePreview() {
            this.isPreviewing = true;
        },

        closeLivePreview() {
            this.isPreviewing = false;
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                this.permalink = response.data.permalink;
                this.values = this.resetValuesFromResponse(response.data.values);
            }
        },
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], (e) => {
            e.preventDefault();
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.quickSave = true;
            this.save();
        });
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));

        container = computed(() => this.$refs.container);
    },

    unmounted() {
        this.saveKeyBinding.destroy();
        this.quickSaveKeyBinding.destroy();
    },
};
</script>
