<template>

    <div class="-mx-1">

        <div class="filter mx-1 mb-2">
            <a href="" :class="{ 'active': activeTab === 'basics' }" @click.prevent="activeTab = 'basics'">{{ translate('cp.basics') }}</a>
            <a href="" :class="{ 'active': activeTab === 'visibility' }" @click.prevent="activeTab = 'visibility'">{{ translate('cp.visibility') }}</a>
            <a href="" :class="{ 'active': activeTab === 'extras' }" @click.prevent="activeTab = 'extras'" v-if="hasExtras">{{ translate('cp.extras') }}</a>
        </div>

        <div class="tab publish-fields" v-show="activeTab === 'basics'">

            <div class="form-group p-2 m-0 w-1/2">
                <label>{{ translate('cp.display_name') }}</label>
                <small class="help-block">{{ translate('cp.display_name_instructions') }}</small>
                <input type="text" class="form-control" v-model="field.display" ref="display" />
            </div>

            <div class="form-group p-2 m-0 w-1/2">
                <label>{{ translate('cp.field_variable') }}</label>
                <small class="help-block">{{ translate('cp.field_variable_instructions') }}</small>
                <input type="text" class="form-control mono" v-model="field.name" @keydown="isNameModified = true" :disabled="field.isMeta" :v-focus="true"/>
            </div>

            <div class="form-group p-2 m-0" v-if="canBeLocalized">
                <label class="block">{{ translate('cp.localizable') }}</label>
                <toggle-fieldtype :data.sync="field.localizable"></toggle-fieldtype>
            </div>

            <div class="form-group p-2 m-0 markdown-fieldtype">
                <label>{{ translate('cp.instructions') }}</label>
                <small class="help-block">{{ translate('cp.field_instructions_instructions') }}</small>
                <markdown-fieldtype :data.sync="field.instructions"></markdown-fieldtype>
            </div>

            <div class="form-group p-2 m-0" v-if="canBeValidated">
                <label>{{ translate('cp.validation_rules') }}</label>
                <small class="help-block">
                    {{ translate('cp.validation_instructions') }}
                    <a href="https://laravel.com/docs/5.1/validation#available-validation-rules" target="_blank">{{ translate('cp.validation_instructions_link_text') }}</a>.
                </small>
                <input type="text" class="form-control" v-model="field.validate" />
            </div>

            <div class="form-group p-2 m-0" v-if="canHaveDefault">
                <label>{{ translate('cp.default_value') }}</label>
                <small class="help-block">{{ translate('cp.field_default_value_instructions') }}</small>
                <input type="text" class="form-control" v-model="field.default" />
            </div>
        </div>

        <div class="tab publish-fields" v-show="activeTab === 'visibility'">
            <div class="form-group p-2 m-0">
                <label class="block">{{ translate('cp.width') }}</label>
                <width-selector :value.sync="field.width" class="large"></width-selector>
            </div>

            <div class="form-group p-2 m-0" v-if="root">
                <label>{{ translate('cp.display_conditions') }}</label>
                <small class="help-block">{{ translate('cp.display_conditions_instructions') }}</small>
                <field-conditions-builder :data.sync="field.conditions"></field-conditions-builder>
            </div>
        </div>

        <div class="tab publish-fields" v-show="activeTab === 'extras'" v-if="hasExtras">

            <div v-for="configField in filteredFieldtypeConfig"
                :class="configFieldClasses(configField)">

                <label class="block">{{ configField.display || configField.name }}</label>
                <small class="help-block" v-if="configField.instructions" v-html="configField.instructions | markdown"></small>

                <component :is="configField.type + '-fieldtype'"
                        :name="$key"
                        :data.sync="field[configField.name]"
                        :config="configField"
                        v-if="configField.name !== 'sets' && configField.name !== 'fields'">
                </component>

                <set-builder :sets.sync="field[configField.name]"
                            :fieldtypes="fieldtypes"
                            v-if="configField.name === 'sets'">
                </set-builder>
            </div>

        </div>

    </div>

</template>

<script>
import WidthSelector from './Sections/WidthSelector.vue';

export default {

    components: {
        fieldConditionsBuilder: require('../field-conditions-builder/FieldConditionsBuilder.vue'),
        WidthSelector
    },

    props: ['field', 'fieldtypeConfig', 'fieldtypes', 'root'],

    data: function() {
        return {
            isNameModified: true,
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
            return _.findWhere(this.fieldtypes, { name: this.field.type });
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
                return _.filter(this.fieldtypeConfig, config => config.name !== 'fields');
            }

            if (['replicator', 'bard'].includes(this.field.type)) {
                return _.filter(this.fieldtypeConfig, config => config.name !== 'sets');
            }

            return this.fieldtypeConfig;
        }
    },

    mounted() {
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

    methods: {

        focus() {
            this.$refs.display.select();
        },

        configFieldClasses(field) {
            return [
                `form-group p-2 m-0 ${field.type}-fieldtype`,
                tailwind_width_class(field.width)
            ];
        }

    }

};
</script>
