<template>
    <div class="h-full overflow-auto bg-content-bg dark:bg-dark-content-bg focus-none p-3 pt-0">
        <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <Icon name="loading" />
        </div>

        <header v-if="!loading" class="flex flex-wrap items-center justify-between pl-3 pt-3 pb-4 -mb-4 sticky top-0 z-(--z-index-modal) bg-gradient-to-b from-white from-75% dark:from-gray-800">
            <Heading :text=" __(fieldtype.title + ' ' + 'Field')" size="lg" :icon="fieldtype.icon" />
            <div class="flex items-center gap-3">
                <Button variant="ghost" :text="__('Cancel')" @click.prevent="close" />
                <Button variant="default" @click.prevent="commit" :text="__('Apply')" />
                <Button v-if="!(isNestedField)" variant="primary" @click.prevent="commitAndSave" icon="save" :text="__('Apply & Save')" />
                <Button v-if="isNestedField" variant="default" @click.prevent="commitAndCloseAll" :text="__('Apply & Close All')" />
                <Button v-if="isNestedField" variant="primary" @click.prevent="commitAndSaveAll" icon="save" :text="__('Save & Close All')" />
            </div>
        </header>

        <section v-if="!loading" class="isolate lg:px-3 py-4">
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
    </div>
</template>

<script>
import { FieldConditionsBuilder, FIELD_CONDITIONS_KEYS } from '../field-conditions/FieldConditions.js';
import FieldValidationBuilder from '../field-validation/Builder.vue';
import { Heading, Button, Tabs, TabList, TabTrigger, TabContent, CardPanel, Icon } from '@/components/ui';

export default {
    components: {
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
        id: String,
        config: Object,
        overrides: { type: Array, default: () => [] },
        type: String,
        root: Boolean,
        fields: Array,
        suggestableConditionFields: Array,
        isInsideSet: Boolean,
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
            default: () => {}
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
    },

    created() {
        this.load();

        // Add keyboard shortcut for Cmd+S / Ctrl+S only when this component is focused
        this.saveBinding = this.$keys.bindGlobal(['mod+s'], (e) => {
            // Only handle if this component is currently visible/focused
            if (this.$el && this.$el.offsetParent !== null) {
                e.preventDefault();
                e.stopPropagation();
                this.handleSaveShortcut();
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
            let { shouldCommitParent, shouldSaveRoot } = params;

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
                        this.commitParentField(params);
                        this.close();

                        return;
                    }

                    if (shouldSaveRoot) {
                        this.saveRootForm();
                    }

                    this.close();
                })
                .catch((e) => this.handleAxiosError(e));
        },

        // Top-level field: saves the current field and the blueprint/fieldset.
        commitAndSave() {
            this.commit({
                shouldSaveRoot: true,
            });
        },

        // Nested field: saves the current field and any parents.
        commitAndCloseAll() {
            this.commit({
                shouldCommitParent: true,
            });
        },

        // Nested field: saves the current field and the blueprint/fieldset.
        commitAndSaveAll() {
            this.commit({
                shouldCommitParent: true,
                shouldSaveRoot: true,
            });
        },

        saveRootForm() {
            // The "root form" could be the blueprint or fieldset forms.
            this.$events.$emit('root-form-save');
        },

        handleSaveShortcut() {
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
                this.$toast.error(__('Something went wrong'));
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
                });
        },
    },
};
</script>
