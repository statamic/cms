<template>

    <div class="flex flex-col h-full">

        <div class="flex items-center p-3 bg-grey-lightest border-b text-center" v-if="fieldtypesLoaded">
            <svg-icon class="h-6 w-6 mr-2 inline-block opacity-50" :name="fieldtype.icon"></svg-icon>
            <span>{{ config.display || config.handle }}</span>
            <span class="text-xs ml-2 font-bold uppercase opacity-25 pt-sm">{{ fieldtype.title }}</span>
        </div>

        <div class="flex-1 overflow-scroll" v-if="fieldtypesLoaded">

            <div class="publish-fields">

                <publish-field :value="config.display" @updated="updateField" :config="{
                    type: 'text',
                    handle: 'display',
                    display: translate('Display'),
                    instructions: translate(`The field's label shown in the Control Panel.`),
                    width: 50
                }" />

                <publish-field
                    :value="config.handle"
                    @updated="(handle, field) => {
                        this.isHandleModified = true;
                        this.updateField(handle, field);
                    }"
                    :config="{
                        type: 'text',
                        handle: 'handle',
                        display: translate('Handle'),
                        instructions: translate(`The field's template variable.`),
                        width: 50
                    }"
                />

                <publish-field :value="config.instructions" @updated="updateField" :config="{
                    type: 'markdown',
                    handle: 'instructions',
                    display: translate('Instructions'),
                    instructions: translate(`Basic Markdown is allowed. Encouraged, even.`),
                }" />

                <publish-field v-if="canBeValidated" :value="config.validate" @updated="updateField" :config="{
                    type: 'text',
                    handle: 'validate',
                    display: translate('Validation Rules'),
                    instructions: translate(`Has access to all of Laravel's validation rules`)
                }" />

                <publish-field v-if="canHaveDefault" :value="config.default" @updated="updateField" :config="{
                    type: 'text',
                    handle: 'default',
                    display: translate('Default Value'),
                    instructions: translate(`Enter the default value for string-type fields.`)
                }" />


                <!--
                <publish-field :value="config.conditions" @updated="updateField" :config="{
                    handle: 'default',
                    display: translate('Display Conditions'),
                    instructions: translate(`Configure when this field will be shown.`)
                }">
                    <template slot="fieldtype">
                        TODO: The field conditions builder will go here.
                    </template>
                </publish-field>
                -->

                <publish-field
                    v-for="configField in filteredFieldtypeConfig"
                    :key="configField.handle"
                    :config="configField"
                    :value="field[configField.handle]"
                    @updated="updateField"
                />

            </div>
        </div>
    </div>

</template>

<script>
import PublishField from '../publish/Field.vue';
import ProvidesFieldtypes from './ProvidesFieldtypes';
import FieldConditionsBuilder from '../field-conditions-builder/FieldConditionsBuilder.vue';

export default {

    components: {
        PublishField,
        FieldConditionsBuilder,
    },

    mixins: [ProvidesFieldtypes],

    props: ['config', 'type', 'root'],

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
            return this.root && Object.keys(Statamic.locales).length > 1 && this.fieldtype.canBeLocalized;
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

    ready: function() {
        var self = this;

        this.root = Boolean(this.root || false);

        // For new fields, we'll slugify the display name into the field name.
        // If they edit the name, we'll stop.
        if (this.config.isNew && !this.config.isMeta) {
            this.isNameModified = false;
            delete this.config.isNew;

            this.$watch('field.display', function(display) {
                if (! this.isNameModified) {
                    this.config.name = this.$slugify(display, '_');
                }
            });
        }
    },

    watch: {

        field(field) {
            this.$emit('input', field);
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
        }

    }

};
</script>
