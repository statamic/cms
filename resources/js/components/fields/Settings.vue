<template>
    <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
        <Icon name="loading" />
    </div>

    <StackHeader v-if="!loading" :title="__(values.display) || __(config.display) || config.handle" :icon="fieldtype.icon">
        <template #actions>
            <Button v-if="!showSaveOnlyAtTopLevel" variant="default" @click.prevent="commit" :text="__('Apply')" />
            <Button v-if="!(isNestedField)" variant="primary" @click.prevent="commitAndSave" icon="save" :text="showSaveOnlyAtTopLevel ? __('Save') : __('Apply & Save')" />
            <Button v-if="isNestedField" variant="default" @click.prevent="commitAndSaveAll" :text="__('Save All')" v-tooltip="saveAllShortcutLabel" />
            <Button v-if="isNestedField" variant="primary" @click.prevent="commitAndSaveTopStack" icon="save" :text="__('Save')" />
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

        selectedWidth: function () {
            var width = this.config.width || 100;
            var found = this.widths.find((w) => w.value === width);
            return found.text;
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

        isNestedField() {
            return this.isInsideSet || this.isInsideConfigFields;
        },

        saveAllShortcutLabel() {
            const platform = typeof navigator !== 'undefined'
                ? (navigator.userAgentData?.platform || navigator.platform || '')
                : '';
            const isMac = /Mac|iPhone|iPad|iPod/i.test(platform);
            return isMac ? 'Cmd+Shift+S' : 'Ctrl+Shift+S';
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

        markFieldEdited(handle) {
            if (this.editedFields.indexOf(handle) === -1) {
                this.editedFields.push(handle);
            }
        },

        commit(params = {}) {
            let { shouldCommitParent, shouldSaveRoot, shouldClose = true } = params;

            this.clearErrors();

            this.$axios
                .post(cp_url('fields/update'), {
                    id: this.id,
                    type: this.type,
                    values: this.values,
                    fields: this.fields,
                    isInsideSet: this.isInsideSet,
                })
                .then((response) => {
                    this.$refs.container?.clearDirtyState();
                    this.$emit('committed', response.data, this.editedFields);

                    if (shouldCommitParent && this.commitParentField) {
						this.$nextTick(() => {
							this.commitParentField(params);
							if (shouldClose) this.close();
						});

                        return;
                    }

                    if (shouldSaveRoot) {
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

            this.$axios
                .post(cp_url('fields/edit'), {
                    type: this.type,
                    reference: field?.type === 'reference' ? field.field_reference : false,
                    values: this.config,
                })
                .then((response) => {
                    this.loading = false;
                    this.fieldtype = response.data.fieldtype;
                    this.blueprint = response.data.blueprint;
                    this.values = response.data.values;
                    this.meta = { ...response.data.meta };
                    this.originValues = response.data.originValues;
                    this.originMeta = response.data.originMeta;
                })
                .catch((e) => {
                    this.loading = false;
                    this.handleAxiosError(e);
                });
        },
    },
};
</script>
