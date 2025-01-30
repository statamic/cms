<template>

    <div class="h-full bg-gray-300 h-full dark:bg-dark-800 overflow-scroll">

        <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center ">
            <loading-graphic />
        </div>

        <header v-if="!loading" class="flex items-center sticky top-0 inset-x-0 bg-white dark:bg-dark-550 shadow dark:shadow-dark px-8 py-2 z-1 h-13">
            <h1 class="flex-1 flex items-center text-xl">
                {{ __(values.display) || __(config.display) || config.handle }}
                <small class="badge-pill bg-gray-100 dark:bg-dark-400 rtl:mr-4 ltr:ml-4 border dark:border-dark-200 text-xs text-gray-700 dark:text-dark-150 font-medium leading-none flex items-center">
                    <svg-icon class="h-4 w-4 rtl:ml-2 ltr:mr-2 inline-block text-gray-700 dark:text-dark-150" :name="fieldtype.icon.startsWith('<svg') ? fieldtype.icon : `light/${fieldtype.icon}`"></svg-icon>
                    {{ fieldtype.title }}
                </small>
            </h1>
            <button
                class="text-gray-700 dark:text-dark-150 hover:text-gray-800 dark:hover:text-dark-100 rtl:ml-6 ltr:mr-6 text-sm"
                @click.prevent="close"
                v-text="__('Cancel')"
            ></button>
            <button
                class="btn-primary"
                @click.prevent="commit"
                v-text="__('Apply')"
            ></button>
        </header>
        <section class="isolate py-4 px-3 md:px-8">
            <div class="tabs-container">
                <div class="publish-tabs tabs">
                    <button class="tab-button"
                    :class="{ 'active': activeTab === 'settings' }"
                        @click="activeTab = 'settings'"
                        v-text="__('Settings')"
                    />
                    <button class="tab-button"
                    :class="{ 'active': activeTab === 'conditions' }"
                        @click="activeTab = 'conditions'"
                        v-text="__('Conditions')"
                    />
                    <button class="tab-button"
                    :class="{ 'active': activeTab === 'validation' }"
                        @click="activeTab = 'validation'"
                        v-text="__('Validation')"
                    />
                </div>
            </div>

            <div v-if="!loading" class="field-settings">

                <publish-container
                    :name="publishContainer"
                    :blueprint="blueprint"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    :is-root="true"
                    @updated="values = $event"
                    v-slot="{ setFieldValue, setFieldMeta }"
                >
                    <div v-show="activeTab === 'settings'">

                        <publish-sections
                            :sections="blueprint.tabs[0].sections"
                            @updated="(handle, value) => updateField(handle, value, setFieldValue)"
                            @meta-updated="setFieldMeta"
                        />

                    </div>
                </publish-container>

                <div class="card p-0" v-show="activeTab === 'conditions'">
                    <div class="publish-fields @container">
                        <field-conditions-builder
                            :config="config"
                            :suggestable-fields="suggestableConditionFields"
                            @updated="updateFieldConditions"
                            @updated-always-save="updateAlwaysSave" />
                    </div>
                </div>

                <div class="card p-0" v-show="activeTab === 'validation'">
                    <div class="publish-fields @container">
                        <field-validation-builder
                            :config="config"
                            @updated="updateField('validate', $event)" />
                    </div>
                </div>

            </div>
        </section>
    </div>

</template>

<script>
import PublishField from '../publish/Field.vue';
import { ValidatesFieldConditions, FieldConditionsBuilder, FIELD_CONDITIONS_KEYS } from '../field-conditions/FieldConditions.js';
import FieldValidationBuilder from '../field-validation/Builder.vue';

export default {

    components: {
        PublishField,
        FieldConditionsBuilder,
        FieldValidationBuilder,
    },

    mixins: [
        ValidatesFieldConditions,
    ],

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
        }
    },

    model: {
        prop: 'config',
        event: 'input'
    },

    data: function() {
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
        publishContainer() {
            return `field-settings-${this.$.uid}`;
        },

        selectedWidth: function() {
            var width = this.config.width || 100;
            var found = _.findWhere(this.widths, {value: width});
            return found.text;
        },

        fieldtypeConfig() {
            return this.fieldtype.config;
        },

        canBeLocalized: function() {
            return this.root && Object.keys(Statamic.$config.get('locales')).length > 1 && this.fieldtype.canBeLocalized;
        },

        canBeValidated: function() {
            return this.fieldtype.canBeValidated;
        },

        canHaveDefault: function() {
            return this.fieldtype.canHaveDefault;
        },

        hasExtras() {
            return this.filteredFieldtypeConfig.length > 0;
        },

        filteredFieldtypeConfig() {
            if (this.type === 'grid') {
                return _.filter(this.fieldtypeConfig, config => config.handle !== 'fields');
            }

            if (['replicator', 'bard'].includes(this.type)) {
                return _.filter(this.fieldtypeConfig, config => config.handle !== 'sets');
            }

            return this.fieldtypeConfig;
        }
    },

    created() {
        this.load();
    },

    methods: {

        configFieldClasses(field) {
            return [
                `form-group p-4 m-0 ${field.type}-fieldtype`,
                tailwind_width_class(field.width)
            ];
        },

        getFieldValue(handle) {
            return this.values[handle];
        },

        updateField(handle, value, setStoreValue=null) {
            this.values[handle] = value;
            this.markFieldEdited(handle);

            if (setStoreValue) {
                setStoreValue(handle, value);
            }
        },

        updateFieldConditions(conditions) {
            let values = {};

            _.each(this.values, (value, key) => {
                if (! FIELD_CONDITIONS_KEYS.includes(key)) {
                    values[key] = value;
                }
            });

            this.values = {...values, ...conditions};

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

        commit() {
            this.clearErrors();

            this.$axios.post(cp_url('fields/update'), {
                id: this.id,
                type: this.type,
                values: this.values,
                fields: this.fields,
                isInsideSet: this.isInsideSet,
            }).then(response => {
                this.$emit('committed', response.data, this.editedFields);
                this.close();
            }).catch(e => this.handleAxiosError(e));
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
            this.$axios.post(cp_url('fields/edit'), {
                type: this.type,
                values: this.config
            }).then(response => {
                this.loading = false;
                this.fieldtype = response.data.fieldtype;
                this.blueprint = response.data.blueprint;
                this.values = response.data.values;
                this.meta = {...response.data.meta};
            })
        }

    }

};
</script>
