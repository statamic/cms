<template>

    <div class="h-full overflow-auto p-4 bg-grey-30 h-full">

        <div v-if="fieldtypesLoading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <div v-if="fieldtypesLoaded" class="flex items-center mb-3 -mt-1">
            <h1 class="flex-1">
                <small class="block text-xs text-grey-70 font-medium leading-none mt-1 flex items-center">
                    <svg-icon class="h-4 w-4 mr-1 inline-block text-grey-70" :name="fieldtype.icon"></svg-icon>
                    {{ fieldtype.title }}
                </small>
                {{ config.display || config.handle }}
            </h1>
            <button
                class="text-grey-50 hover:text-grey-80 mr-3 text-sm"
                @click.prevent="close"
                v-text="__('Cancel')"
            ></button>
            <button
                class="btn btn-primary"
                @click.prevent="commit"
                v-text="__('Finish')"
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
                    fieldtype="text"
                    handle="instructions"
                    :display="__('Instructions')"
                    :instructions="__(`Shown under the field's display label, this like very text. Markdown is supported.`)"
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
                    @updated="updateField('validate', $event)" />

                <publish-field
                    v-for="configField in filteredFieldtypeConfig"
                    :key="configField.handle"
                    :config="configField"
                    :value="values[configField.handle]"
                    @input="updateField"
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

    props: ['config', 'overrides', 'type', 'root', 'suggestableConditionFields'],

    model: {
        prop: 'config',
        event: 'input'
    },

    data: function() {
        return {
            values: clone(this.config),
            editedFields: clone(this.overrides),
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
            return _.findWhere(this.fieldtypes, { handle: this.type || 'text' })
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
                    this.updateField('handle', this.config.handle);
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
            this.values[handle] = value;
            this.markFieldEdited(handle);
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

        markFieldEdited(handle) {
            if (this.editedFields.indexOf(handle) === -1) {
                this.editedFields.push(handle);
            }
        },

        commit() {
            this.$emit('committed', this.values, this.editedFields);
            this.close();
        },

        close() {
            this.$emit('closed');
        }

    }

};
</script>
