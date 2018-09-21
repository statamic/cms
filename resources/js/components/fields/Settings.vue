<template>

    <div class="flex flex-col h-full">

        <div class="flex items-center p-3 bg-grey-lightest border-b text-center">
            <svg-icon class="h-6 w-6 mr-2 inline-block opacity-50" :name="fieldtype.icon"></svg-icon>
            <span>{{ field.display || field.handle }}</span>
            <span class="text-xs ml-2 font-bold uppercase opacity-25 pt-sm">{{ fieldtype.title }}</span>
        </div>

        <div class="flex-1 overflow-scroll">

            <div class="publish-fields">

                <publish-field :value="field.display" @updated="updateField" :config="{
                    type: 'text',
                    handle: 'display',
                    display: translate('Display'),
                    instructions: translate(`The field's label shown in the Control Panel.`),
                    width: 50
                }" />

                <publish-field
                    :value="field.handle"
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

                <publish-field :value="field.instructions" @updated="updateField" :config="{
                    type: 'markdown',
                    handle: 'instructions',
                    display: translate('Instructions'),
                    instructions: translate(`Basic Markdown is allowed. Encouraged, even.`),
                }" />

                <publish-field v-if="canBeValidated" :value="field.validate" @updated="updateField" :config="{
                    type: 'text',
                    handle: 'validate',
                    display: translate('Validation Rules'),
                    instructions: translate(`Has access to all of Laravel's validation rules`)
                }" />

                <publish-field v-if="canHaveDefault" :value="field.default" @updated="updateField" :config="{
                    type: 'text',
                    handle: 'default',
                    display: translate('Default Value'),
                    instructions: translate(`Enter the default value for string-type fields.`)
                }" />

                <publish-field :value="field.conditions" @updated="updateField" :config="{
                    handle: 'default',
                    display: translate('Display Conditions'),
                    instructions: translate(`Configure when this field will be shown.`)
                }">
                    <template slot="fieldtype">
                        TODO: The field conditions builder will go here.
                        <!-- <field-conditions-builder v-model="field.conditions" /> -->
                    </template>
                </publish-field>

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

    props: ['field', 'root'],

    model: {
        prop: 'field',
        event: 'input'
    },

    data: function() {
        return {
            values: this.field,
            isHandleModified: true,
            activeTab: 'basics'
        };
    },

    computed: {
        selectedWidth: function() {
            var width = this.field.width || 100;
            var found = _.findWhere(this.widths, {value: width});
            return found.text;
        },

        fieldtype: function() {
            return _.findWhere(this.fieldtypes, { handle: this.field.type });
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
            if (this.field.type === 'grid') {
                return _.filter(this.fieldtypeConfig, config => config.handle !== 'fields');
            }

            if (['replicator', 'bard'].includes(this.field.type)) {
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
        if (this.field.isNew && !this.field.isMeta) {
            this.isNameModified = false;
            delete this.field.isNew;

            this.$watch('field.display', function(display) {
                if (! this.isNameModified) {
                    this.field.name = this.$slugify(display, '_');
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
            const field = this.values;
            field[handle] = value;
            this.$emit('input', field);
        }

    }

};
</script>
