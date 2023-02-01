<template>

    <div class="h-full overflow-auto p-4 bg-grey-30 h-full">

        <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <div v-if="!loading" class="flex items-center mb-3 -mt-1">
            <h1 class="flex-1">
                <small class="block text-xs text-grey-70 font-medium leading-none mt-1 flex items-center">
                    <svg-icon class="h-4 w-4 mr-1 inline-block text-grey-70" :name="fieldtype.icon"></svg-icon>
                    {{ fieldtype.title }}
                </small>
                {{ values.display || config.display || config.handle }}
            </h1>
            <button
                class="text-grey-70 hover:text-grey-80 mr-3 text-sm"
                @click.prevent="close"
                v-text="__('Cancel')"
            ></button>
            <button
                class="btn-primary"
                @click.prevent="commit"
                v-text="__('Finish')"
            ></button>
        </div>

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

        <div class="card rounded-tl-none" v-if="!loading">

            <publish-container
                :name="publishContainer"
                :blueprint="blueprint"
                :values="values"
                :meta="meta"
                :errors="errors"
                :is-root="true"
                @updated="values = $event"
            >
                <div v-show="activeTab === 'settings'" slot-scope="{ setFieldValue, setFieldMeta }">

                    <publish-fields
                        v-if="blueprint.sections.length"
                        class="w-full"
                        :fields="blueprint.sections[0].fields"
                        @updated="(handle, value) => {
                            updateField(handle, value, setFieldValue);
                            if (handle === 'handle') isHandleModified = true
                        }"
                        @meta-updated="setFieldMeta"
                    />

                </div>
            </publish-container>

            <div class="publish-fields" v-show="activeTab === 'conditions'">
                <field-conditions-builder
                    :config="config"
                    :suggestable-fields="suggestableConditionFields"
                    @updated="updateFieldConditions"
                    @updated-always-save="updateAlwaysSave" />
            </div>

            <div class="publish-fields" v-show="activeTab === 'validation'">
                <field-validation-builder
                    :config="config"
                    @updated="updateField('validate', $event)" />

            </div>
        </div>
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
        config: Object,
        overrides: { type: Array, default: () => [] },
        type: String,
        root: Boolean,
        suggestableConditionFields: Array,
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
            isHandleModified: true,
            activeTab: 'settings',
            storeName: 'base',
            fieldtype: null,
            loading: true,
            blueprint: null,
        };
    },

    computed: {
        publishContainer() {
            return `field-settings-${this._uid}`;
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
        // For new fields, we'll slugify the display name into the field name.
        // If they edit the handle, we'll stop.
        if (this.config.isNew && !this.config.isMeta) {
            this.isHandleModified = false;

            this.$watch('values.display', function(display) {
                if (! this.isHandleModified) {
                    const handle = this.$slugify(display, '_');
                    this.updateField('handle', handle);
                }
            });
        }

        this.load();
    },

    methods: {

        configFieldClasses(field) {
            return [
                `form-group p-2 m-0 ${field.type}-fieldtype`,
                tailwind_width_class(field.width)
            ];
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
        },

        markFieldEdited(handle) {
            if (this.editedFields.indexOf(handle) === -1) {
                this.editedFields.push(handle);
            }
        },

        commit() {
            this.clearErrors();

            this.$axios.post(cp_url('fields/update'), {
                type: this.type,
                values: this.values
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
