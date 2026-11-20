<template>
    <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
        <Icon name="loading" />
    </div>

    <StackHeader v-if="!loading" :title="__(values.display) || __(config.display) || config.handle" :icon="fieldtype.icon">
        <template #actions>
            <Button v-if="!canSaveRoot" variant="primary" @click.prevent="commit" :text="__('Apply')" />
            <Button v-if="canSaveRoot && !showSaveOnlyAtTopLevel" variant="default" @click.prevent="commit" :text="__('Apply')" />
            <Button v-if="canSaveRoot && !isNestedField" variant="primary" @click.prevent="commitAndSave" icon="save" :text="showSaveOnlyAtTopLevel ? __('Save') : __('Apply & Save')" v-tooltip="saveAndCloseShortcutLabel" />
            <Button v-if="canSaveRoot && isNestedField" variant="default" @click.prevent="commitAndSaveAll" :text="__('Save All')" v-tooltip="saveAllShortcutLabel" />
            <Button v-if="canSaveRoot && isNestedField" variant="primary" @click.prevent="commitAndSaveTopStack" icon="save" :text="__('Save')" />
        </template>
    </StackHeader>

    <StackContent>
        <section v-if="!loading" class="isolate">
            <Tabs v-model:modelValue="activeTab">
                <TabList class="mb-6">
                    <TabTrigger name="settings" :text="__('Settings')" />
                    <TabTrigger name="conditions" :text="__('Conditions')" />
                    <TabTrigger name="validation" :text="__('Validation')" />
                </TabList>

                <div>
                    <TabContent name="settings">
                        <ui-publish-container
                            ref="container"
                            :blueprint="adjustedBlueprint"
                            :meta="meta"
                            :errors="errors"
                            v-model="values"
                            v-model:modified-fields="editedFields"
                            :origin-values="originValues"
                            :origin-meta="originMeta"
                            as-config
                        />
                    </TabContent>

                    <TabContent name="conditions">
                        <CardPanel :heading="__('Conditions')">
                            <FieldConditionsBuilder
                                :config="values"
                                :suggestable-fields="suggestableConditionFields"
                                @updated="updateFieldConditions"
                                @updated-always-save="updateAlwaysSave"
                                @updated-reserve-space-when-hidden="updateReserveSpaceWhenHidden"
                            />
                        </CardPanel>
                    </TabContent>

                    <TabContent name="validation">
                        <CardPanel :heading="__('Validation')">
                            <FieldValidationBuilder :config="values" @updated="updateField('validate', $event)" />
                        </CardPanel>
                    </TabContent>
                </div>
            </Tabs>
        </section>
    </StackContent>
</template>

<script>
import { FieldConditionsBuilder, FIELD_CONDITIONS_KEYS } from '../field-conditions/FieldConditions.js';
import FieldValidationBuilder from '../field-validation/Builder.vue';
import { Heading, Button, Tabs, TabList, TabTrigger, TabContent, CardPanel, Icon, StackHeader, StackContent } from '@/components/ui';
import shortcutLabel from '@/util/shortcutLabel.js';

export default {
    emits: ['committed', 'closed'],

    components: {
        StackContent,
        StackHeader,
        FieldConditionsBuilder,
        FieldValidationBuilder,
        Heading,
        Button,
        Tabs,
        TabList,
        TabTrigger,
        TabContent,
        CardPanel,
        Icon
    },

    props: {
        id: [String, Number],
        config: Object,
        overrides: { type: Array, default: () => [] },
        type: String,
        root: Boolean,
        fields: Array,
        suggestableConditionFields: Array,
        isInsideSet: Boolean,
        showSaveOnlyAtTopLevel: {
            type: Boolean,
            default: false,
        },
    },

    provide() {
        return {
            isInsideConfigFields: true,
            updateFieldSettingsValue: this.updateField,
            getFieldSettingsValue: this.getFieldValue,
            commitParentField: this.commit,
        };
    },

    inject: {
        isInsideConfigFields: {
            default: false
        },
        commitParentField: {
            default: null
        },
        fieldSettingsApi: {
            default: null
        }
    },

    model: {
        prop: 'config',
        event: 'input',
    },

    data: function () {
        return {
            values: null,
            meta: null,
            originValues: null,
            originMeta: null,
            error: null,
            errors: {},
            editedFields: clone(this.overrides),
            activeTab: 'settings',
            fieldtype: null,
            loading: true,
            blueprint: null,
            isSaving: false, // Prevent multiple simultaneous saves
        };
    },

    computed: {
        adjustedBlueprint() {
            let blueprint = this.blueprint;

            blueprint.tabs = [blueprint.tabs[0]]; // Only the first tab is supported/necessary.

            // Make all fields localizable so they can be edited.
            // Fields are non-localizable by default, but this UI requires all fields to be editable.
            blueprint.tabs[0].sections.forEach((section, sectionIndex) => {
                section.fields.forEach((field, fieldIndex) => {
                    blueprint.tabs[0].sections[sectionIndex].fields[fieldIndex].localizable = true;
                });
            });

            return blueprint;
        },

        fieldtypeConfig() {
            return this.fieldtype.config;
        },

        canBeLocalized: function () {
            return (
                this.root && Object.keys(Statamic.$config.get('locales')).length > 1 && this.fieldtype.canBeLocalized
            );
        },

        canBeValidated: function () {
            return this.fieldtype.canBeValidated;
        },

        canHaveDefault: function () {
            return this.fieldtype.canHaveDefault;
        },

        hasExtras() {
            return this.filteredFieldtypeConfig.length > 0;
        },

        filteredFieldtypeConfig() {
            if (this.type === 'grid') {
                return this.fieldtypeConfig.filter((config) => config.handle !== 'fields');
            }

            if (['replicator', 'bard'].includes(this.type)) {
                return this.fieldtypeConfig.filter((config) => config.handle !== 'sets');
            }

            return this.fieldtypeConfig;
        },

        canSaveRoot() {
            return this.fieldSettingsApi?.canSaveRoot ?? true;
        },

        isNestedField() {
            return this.isInsideSet || this.isInsideConfigFields;
        },

        saveAndCloseShortcutLabel() {
            return __('Save & Close (:shortcut)', { shortcut: shortcutLabel('mod+shift+s') });
        },

        saveAllShortcutLabel() {
            return __('Save All (:shortcut)', { shortcut: shortcutLabel('mod+shift+s') });
        },
    },

    created() {
        this.load();

        // Add keyboard shortcuts only when this component is focused.
        this.saveBinding = this.$keys.bindGlobal(['mod+s', 'mod+shift+s'], (e) => {
            // Only handle if this component is currently visible/focused
            if (this.$el && this.$el.offsetParent !== null) {
                e.preventDefault();
                e.stopPropagation();
                this.handleSaveShortcut(e);
            }
        });
    },

    beforeUnmount() {
        // Clean up keyboard binding
        if (this.saveBinding) {
            this.saveBinding.destroy();
        }
    },

    methods: {
        getFieldValue(handle) {
            return this.values[handle];
        },

        updateField(handle, value, setStoreValue = null) {
            this.values[handle] = value;
            this.markFieldEdited(handle);

            if (setStoreValue) {
                setStoreValue(handle, value);
            }
        },

        updateFieldConditions(conditions) {
            let values = {};

            Object.entries(this.values).forEach(([key, value]) => {
                if (!FIELD_CONDITIONS_KEYS.includes(key)) {
                    values[key] = value;
                }
            });

            this.values = { ...values, ...conditions };

            if (Object.keys(conditions).length > 0) {
                this.markFieldEdited(Object.keys(conditions)[0]);
            }
        },

        updateAlwaysSave(alwaysSave) {
            this.values.always_save = alwaysSave;

            this.markFieldEdited('always_save');
        },

        updateReserveSpaceWhenHidden(reserveSpaceWhenHidden) {
            this.values.reserve_space_when_hidden = reserveSpaceWhenHidden;

            this.markFieldEdited('reserve_space_when_hidden');
        },

        markFieldEdited(handle) {
            if (this.editedFields.indexOf(handle) === -1) {
                this.editedFields.push(handle);
            }
        },

        commit(params = {}) {
            let { shouldCommitParent, shouldSaveRoot, shouldClose = true } = params;

            this.clearErrors();

            const update = this.fieldSettingsApi?.update
                ?? ((payload) => this.$axios.post(cp_url('fields/update'), payload).then((response) => response.data));

            update({
                id: this.id,
                type: this.type,
                values: this.values,
                fields: this.fields,
                isInsideSet: this.isInsideSet,
            })
                .then((values) => {
                    this.$refs.container?.clearDirtyState();
                    this.$emit('committed', values, this.editedFields);

                    if (shouldCommitParent && this.commitParentField) {
						this.$nextTick(() => {
							this.commitParentField(params);
							if (shouldClose) this.close();
						});

                        return;
                    }

                    if (shouldSaveRoot && this.canSaveRoot) {
                        this.saveRootForm();
                    }

                    if (shouldClose) this.close();
                })
                .catch((e) => this.handleAxiosError(e));
        },

        // Top-level field: saves the current field and the blueprint/fieldset.
        commitAndSave() {
            this.commit({
                shouldSaveRoot: true,
            });
        },

        // Nested field: saves the current field and the blueprint/fieldset.
        commitAndSaveAll() {
            this.commit({
                shouldCommitParent: true,
                shouldSaveRoot: true,
            });
        },

        // Nested field: saves and closes only the current stack.
        commitAndSaveTopStack() {
            this.commit({
                shouldSaveRoot: !this.isNestedField,
            });
        },

        softSave() {
            if (this.config.isNew) {
                this.isNestedField ? this.commitAndSaveTopStack() : this.commitAndSave();
                return;
            }

            this.commit({
                shouldCommitParent: this.isNestedField,
                shouldSaveRoot: true,
                shouldClose: false,
            });
        },

        saveRootForm() {
            // The "root form" could be the blueprint or fieldset forms.
            this.$events.$emit('root-form-save');
        },

        handleSaveShortcut(event) {
            if (event?.key?.toLowerCase() === 's' && event?.shiftKey) {
                this.saveAllShortcut();
                return;
            }

            this.softSave();
        },

        saveAllShortcut() {
            this.isNestedField
                ? this.commitAndSaveAll()
                : this.commitAndSave();
        },

        handleAxiosError(e) {
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                this.$toast.error(e.response?.data?.message || __('Something went wrong'));
            }
        },

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        close() {
            this.$emit('closed');
        },

        load() {
            const field = this.fields.find(f => f.handle === this.config.handle);

            const edit = this.fieldSettingsApi?.edit
                ?? ((payload) => this.$axios.post(cp_url('fields/edit'), payload).then((response) => response.data));

            edit({
                type: this.type,
                reference: field?.type === 'reference' ? field.field_reference : false,
                values: this.config,
            })
                .then((data) => {
                    this.loading = false;
                    this.fieldtype = data.fieldtype;
                    this.blueprint = data.blueprint;
                    this.values = data.values;
                    this.meta = { ...data.meta };
                    this.originValues = data.originValues;
                    this.originMeta = data.originMeta;
                })
                .catch((e) => {
                    this.loading = false;
                    this.handleAxiosError(e);
                });
        },
    },
};
</script>
