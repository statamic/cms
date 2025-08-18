<template>
    <div>
        <Header :title="__(title)" icon="globals">
            <Dropdown v-if="canConfigure || canEditBlueprint">
                <template #trigger>
                    <Button icon="ui/dots" variant="ghost" :aria-label="__('Open dropdown menu')" />
                </template>
                <DropdownMenu>
                    <DropdownItem :text="__('Configure')" icon="cog" v-if="canConfigure" :href="configureUrl" />
                    <DropdownItem :text="__('Edit Blueprint')" icon="blueprint-edit" v-if="canEditBlueprint" :href="actions.editBlueprint" />
                </DropdownMenu>
            </Dropdown>

            <ui-badge icon="padlock-locked" :text="__('Read Only')" variant="flat" v-if="!canEdit" />

            <SiteSelector
                v-if="showLocalizationSelector"
                class="ltr:mr-4 rtl:ml-4"
                :sites="localizations"
                :value="site"
                @input="localizationSelected"
            />

            <div class="hidden items-center gap-3 md:flex">
                <Button
                    v-if="canEdit"
                    variant="primary"
                    :text="__('Save')"
                    :disabled="!canSave"
                    @click.prevent="save"
                />
            </div>

            <slot name="action-buttons-right" />
        </Header>

        <div
            v-if="fieldset.empty"
            class="px-8 py-16 border border-dashed border-gray-400 dark:border-gray-600 rounded-lg text-center"
        >
            <ui-heading class="mx-auto max-w-md" :text="__('messages.global_set_no_fields_description')" />
        </div>

        <PublishContainer
            v-if="fieldset && !fieldset.empty"
            ref="container"
            :name="publishContainer"
            :reference="initialReference"
            :blueprint="fieldset"
            v-model="values"
            :meta="meta"
            :errors="errors"
            :site="site"
            :localized-fields="localizedFields"
            :sync-field-confirmation-text="syncFieldConfirmationText"
        />

        <confirmation-modal
            v-if="pendingLocalization"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Continue')"
            :danger="true"
            @confirm="confirmSwitchLocalization"
            @cancel="pendingLocalization = null"
        />
    </div>
</template>

<script>
import SiteSelector from '../SiteSelector.vue';
import clone from '@/util/clone.js';
import { Button, Dropdown, DropdownItem, DropdownMenu, Header } from '@/components/ui';
import PublishContainer from '@/components/ui/Publish/Container.vue';
import PublishTabs from '@/components/ui/Publish/Tabs.vue';
import PublishComponents from '@/components/ui/Publish/Components.vue';
import { computed, ref } from 'vue';
import { Pipeline, Request, BeforeSaveHooks, AfterSaveHooks, PipelineStopped } from '@/components/ui/Publish/SavePipeline.js';

let saving = ref(false);
let errors = ref({});
let container = null;

export default {
    components: {
        PublishComponents,
        PublishContainer,
        PublishTabs,
        Dropdown,
        DropdownItem,
        Button,
        DropdownMenu,
        Header,
        SiteSelector,
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
        canEdit: Boolean,
        canConfigure: Boolean,
        configureUrl: String,
        canEditBlueprint: Boolean,
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
            readOnly: this.initialReadOnly,
            syncFieldConfirmationText: __('messages.sync_entry_field_confirmation_text'),
            pendingLocalization: null,
        };
    },

    computed: {
        saving() {
            return saving.value;
        },

        errors() {
            return errors.value;
        },

        somethingIsLoading() {
            return !this.$progress.isComplete();
        },

        canSave() {
            return !this.readOnly && !this.somethingIsLoading;
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

        originLocalization() {
            return this.localizations.find((l) => l.origin);
        },
    },

    watch: {
        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-global-publish-form`, saving);
        },
    },

    methods: {
        save() {
            if (!this.canSave) return;

            new Pipeline()
                .provide({ container, errors, saving })
                .through([
                    new BeforeSaveHooks('global-set', {
                        globalSet: this.initialHandle,
                        values: this.values,
                    }),
                    new Request(this.actions.save, this.method, {
                        _blueprint: this.fieldset.handle,
                        _localized: this.localizedFields,
                    }),
                    new AfterSaveHooks('global-set', {
                        globalSet: this.initialHandle,
                        reference: this.initialReference,
                    })
                ])
                .then((response) => {
                    if (!this.isCreating) this.$toast.success(__('Saved'));

                    this.$nextTick(() => this.$emit('saved', response));
                })
                .catch((e) => {
                    if (!(e instanceof PipelineStopped)) {
                        this.$toast.error(__('Something went wrong'));
                        console.error(e);
                    }
                });
        },

        localizationSelected(localizationHandle) {
            let localization = this.localizations.find((localization) => localization.handle === localizationHandle);

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
            this.localizing = localization.handle;

            if (this.publishContainer === 'base') {
                window.history.replaceState({}, '', localization.url);
            }

            this.$axios.get(localization.url).then((response) => {
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
            });
        },

        localizationStatusText(localization) {
            return localization.exists
                ? 'This global set exists in this site.'
                : 'This global set does not exist for this site.';
        },
    },

    mounted() {
        this.$keys.bindGlobal(['mod+s'], (e) => {
            e.preventDefault();
            this.save();
        });
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));

        container = computed(() => this.$refs.container);
    },
};
</script>
