<template>

    <div class="h-full overflow-auto p-4 bg-grey-30 h-full">

        <div v-if="fieldtypesLoading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <div v-if="fieldtypesLoaded" class="flex items-center mb-3 -mt-1">
            <h1 class="flex-1">
                {{ config.display || config.handle }}
                <small class="block text-xs text-grey-40 font-medium leading-none mt-1 flex items-center">
                    <svg-icon class="h-4 w-4 mr-1 inline-block text-grey-40 text-current" :name="fieldtype.icon"></svg-icon>
                    {{ fieldtype.title }}
                </small>
            </h1>
            <button
                class="btn btn-primary"
                @click.prevent="$emit('closed')"
                v-text="__('Done')"
            ></button>
        </div>

        <div class="card" v-if="fieldtypesLoaded">

            <div class="publish-fields">

                <form-group
                    handle="display"
                    :display="__('Display')"
                    :instructions="__(`The field's label shown in the Control Panel.`)"
                    width="50"
                    autofocus
                    :value="config.display"
                    @input="updateField('display', $event)"
                />

                <form-group
                    handle="handle"
                    :display="__('Handle')"
                    :instructions="__(`The field's template variable.`)"
                    width="50"
                    :value="config.handle"
                    @input="(field) => {
                        this.isHandleModified = true;
                        this.updateField('handle', field);
                    }"
                />

                <form-group
                    fieldtype="markdown"
                    handle="instructions"
                    :display="__('Instructions')"
                    :instructions="__(`Basic Markdown is allowed. Encouraged, even.`)"
                    :value="config.instructions"
                    @input="updateField('instructions', $event)"
                />

                <!--
                    TODO:
                    - Validation
                    - Default value
                -->

                <field-conditions-builder
                    :config="config"
                    :suggestable-fields="suggestableConditionFields"
                    @updated="updateFieldConditions" />

                <field-validation-builder
                    :config="config"
                    @updated="updateFieldValidation" />

                <publish-field
                    v-for="configField in filteredFieldtypeConfig"
                    :key="configField.handle"
                    :config="configField"
                    :value="values[configField.handle]"
                    @updated="updateField"
                />

            </div>
        </div>
    </div>

</template>

<script>
import PublishField from '../publish/Field.vue';
import ProvidesFieldtypes from './ProvidesFieldtypes';
import { FieldConditionsBuilder, FIELD_CONDITIONS_KEYS } from '../field-conditions/FieldConditions.js';
import FieldValidationBuilder from '../field-validation/Builder.vue';

export default {

    components: {
        PublishField,
        FieldConditionsBuilder,
        FieldValidationBuilder,
    },

    mixins: [ProvidesFieldtypes],

    props: ['config', 'type', 'root', 'suggestableConditionFields'],

    model: {
        prop: 'config',
        event: 'input'
    },

    data: function() {
        return {
            values: this.config,
            isHandleModified: true,
            activeTab: 'basics'
        };
    },

    computed: {
        selectedWidth: function() {
            var width = this.config.width || 100;
            var found = _.findWhere(this.widths, {value: width});
            return found.text;
        },

        fieldtype: function() {
            return _.findWhere(this.fieldtypes, { handle: this.type });
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
            delete this.config.isNew;

            this.$watch('config.display', function(display) {
                if (! this.isHandleModified) {
                    this.config.handle = this.$slugify(display, '_');
                }
            });
        }
    },

    methods: {

        focus() {
            this.$els.display.select();
        },

        configFieldClasses(field) {
            return [
                `form-group p-2 m-0 ${field.type}-fieldtype`,
                tailwind_width_class(field.width)
            ];
        },

        updateField(handle, value) {
            const values = this.values;
            values[handle] = value;
            this.$emit('input', values);
            this.$emit('updated', handle, value);
        },

        updateFieldConditions(conditions) {
            let values = {};

            _.each(this.values, (value, key) => {
                if (! FIELD_CONDITIONS_KEYS.includes(key)) {
                    values[key] = value;
                }
            });

            this.$emit('input', {...values, ...conditions});
        },

        updateFieldValidation(rules) {
            const values = this.values;

            if (rules) {
                values.validate = rules;
            } else {
                delete values.validate;
            }

            this.$emit('input', values);
        }

    }

};
</script>
