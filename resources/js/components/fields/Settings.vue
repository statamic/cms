<template>
    <div class="h-full overflow-auto rounded-l-xl bg-white p-3 dark:bg-gray-800">
        <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <header v-if="!loading" class="flex items-center justify-between pl-3">
            <Heading
                :text="__(values.display) || __(config.display) || config.handle"
                size="lg"
                :icon="fieldtype.icon.startsWith('<svg') ? fieldtype.icon : `fieldtype-${fieldtype.icon}`"
            />
            <div class="flex items-center gap-3">
                <Button variant="ghost" :text="__('Cancel')" @click.prevent="close" />
                <Button variant="primary" @click.prevent="commit()" :text="__('Apply')" />
                <Button
                    v-if="isInsideSet"
                    variant="primary"
                    @click.prevent="commit(true)"
                    :text="__('Apply & Close All')"
                />
            </div>
        </header>

        <section class="isolate px-3 py-4">
            <Tabs v-model:modelValue="activeTab">
                <TabList class="mb-6">
                    <TabTrigger name="settings" :text="__('Settings')" />
                    <TabTrigger name="conditions" :text="__('Conditions')" />
                    <TabTrigger name="validation" :text="__('Validation')" />
                </TabList>

                <div v-if="!loading">
                    <publish-container
                        :name="`field-settings-${$.uid}`"
                        :blueprint="blueprint"
                        :values="values"
                        :meta="meta"
                        :errors="errors"
                        :is-root="true"
                        @updated="values = $event"
                        v-slot="{ setFieldValue, setFieldMeta }"
                    >
                        <TabContent name="settings">
                            <publish-sections
                                :sections="blueprint.tabs[0].sections"
                                @updated="(handle, value) => updateField(handle, value, setFieldValue)"
                                @meta-updated="setFieldMeta"
                            />
                        </TabContent>
                    </publish-container>

                    <TabContent name="conditions">
                        <CardPanel :heading="__('Conditions')">
                            <FieldConditionsBuilder
                                :config="config"
                                :suggestable-fields="suggestableConditionFields"
                                @updated="updateFieldConditions"
                                @updated-always-save="updateAlwaysSave"
                            />
                        </CardPanel>
                    </TabContent>

                    <TabContent name="validation">
                        <CardPanel :heading="__('Validation')">
                            <FieldValidationBuilder :config="config" @updated="updateField('validate', $event)" />
                        </CardPanel>
                    </TabContent>
                </div>
            </Tabs>
        </section>
    </div>
</template>

<script>
import PublishField from '../publish/Field.vue';
import { FieldConditionsBuilder, FIELD_CONDITIONS_KEYS } from '../field-conditions/FieldConditions.js';
import FieldValidationBuilder from '../field-validation/Builder.vue';
import { Heading, Button, Tabs, TabList, TabTrigger, TabContent, CardPanel } from '@statamic/ui';

export default {
    components: {
        PublishField,
        FieldConditionsBuilder,
        FieldValidationBuilder,
        Heading,
        Button,
        Tabs,
        TabList,
        TabTrigger,
        TabContent,
        CardPanel,
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
        commitParentField: {
            default: () => {},
        },
    },

    model: {
        prop: 'config',
        event: 'input',
    },

    data: function () {
        return {
            values: null,
            meta: null,
            error: null,
            errors: {},
            editedFields: clone(this.overrides),
            activeTab: 'settings',
            fieldtype: null,
            loading: true,
            blueprint: null,
        };
    },

    computed: {
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
    },

    created() {
        this.load();
    },

    methods: {
        configFieldClasses(field) {
            return [`form-group p-4 m-0 ${field.type}-fieldtype`, field_width_class(field.width)];
        },

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

        commit(shouldCommitParent = false) {
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
                    this.$emit('committed', response.data, this.editedFields);
                    this.close();

                    if (shouldCommitParent && this.commitParentField) {
                        this.commitParentField(true);
                    }
                })
                .catch((e) => this.handleAxiosError(e));
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
            this.$axios
                .post(cp_url('fields/edit'), {
                    type: this.type,
                    values: this.config,
                })
                .then((response) => {
                    this.loading = false;
                    this.fieldtype = response.data.fieldtype;
                    this.blueprint = response.data.blueprint;
                    this.values = response.data.values;
                    this.meta = { ...response.data.meta };
                });
        },
    },
};
</script>
