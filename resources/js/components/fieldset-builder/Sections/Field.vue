<template>

    <div :class="classes">
        <div class="section-field-inner">
            <div class="section-field-main">
                <div :class="[
                    'field-drag-handle',
                    `field-drag-handle--${parentKey}`,
                    { 'root-level-drag-handle': isRootLevel }
                ]"></div>
                <div class="section-field-main-inner">
                <div class="field-info">
                    <div class="flex items-center">
                        <div class="h-4 w-4">
                            <svg-icon class="h-4 w-4 opacity-50 self-center" :name="icon"></svg-icon>
                        </div>
                        <div class="ml-2">
                            <span class="field-display-sizer" ref="field-display-sizer" v-text="field.display || field.name"></span>
                            <span class="field-name-sizer" ref="field-name-sizer" v-text="field.name"></span>
                            <input class="field-display" ref="display" type="text" v-model="field.display" :placeholder="field.display || field.name" :style="{width: displayFieldWidth}" />
                            <input class="field-name" ref="name" type="text" v-model="field.name" :style="{width: nameFieldWidth}" @input="handleModified = true" :disabled="field.isMeta" />
                            <div class="field-type">{{ fieldtypeLabel }}</div>
                        </div>
                    </div>
                </div>
                <div class="field-controls">
                    <width-selector :value.sync="field.width" v-tip :tip-text="translate('cp.width')"></width-selector>
                    <template v-if="canBeLocalized">
                        <a @click="field.localizable = !field.localizable" v-tip :tip-text="translate('cp.localizable')"><span class="icon icon-globe field-localizable" :class="{ 'is-localizable': field.localizable }"></span></a>
                    </template>
                    <a @click="edit" v-tip :tip-text="translate('cp.edit')"><span class="icon icon-pencil field-edit"></span></a>
                    <a @click="$emit('removed')" v-tip :tip-text="translate('cp.delete')"><span class="field-delete icon icon-cross delete"></span></a>
                    <template v-if="isGridField">
                        <a @click="addField" v-tip :tip-text="translate('cp.add_field')"><span class="icon icon-plus field-add-grid-field"></span></a>
                        <a @click="isShowingGridFields = !isShowingGridFields" v-tip :tip-text="translate('cp.toggle_fields')" v-show="field.fields.length" class="always-visible"><span class="icon icon-chevron-{{ isShowingGridFields ? 'up' : 'down' }} field-expand"></span></a>
                    </template>
                    <template v-if="isReplicatorField">
                        <a @click="addReplicatorSet" v-tip :tip-text="translate('cp.add_set')"><span class="icon icon-plus field-add-set"></span></a>
                        <a @click="isShowingSets = !isShowingSets" v-tip :tip-text="translate('cp.toggle_sets')" v-show="field.sets.length" class="always-visible"><span class="icon icon-chevron-{{ isShowingSets ? 'up' : 'down' }} field-expand"></span></a>
                    </template>
                </div>
                </div>
            </div>

            <div class="field-nested field-fields" v-if="isGridField" v-show="isShowingGridFields">
                <fieldset-fields
                    ref="fields"
                    :fields.sync="field.fields"
                    :section="section"
                    :parent-key="fieldKey"
                    :fieldtypes="fieldtypes"
                    :is-adding="isAddingGridField"
                    @selector-closed="fieldSelectorClosed"
                ></fieldset-fields>

                <a class="btn btn-default btn-small mt-16" @click="addField">{{ translate('cp.add_field') }}</a>
            </div>

            <replicator-sets
                v-if="isReplicatorField"
                v-show="isShowingSets"
                ref="sets"
                :sets.sync="field.sets"
                :section="section"
                :fieldtypes="fieldtypes"
                :parent-key="fieldKey"
            ></replicator-sets>
        </div>

        <modal :show.sync="isEditing" class="modal-wide">
            <template slot="header">
                <div class="flex items-center">
                    <svg-icon class="h-6 w-6 mr-2 inline-block opacity-50" :name="icon"></svg-icon>
                    <span>{{ field.display || field.name }}</span>
                    <span class="text-xs ml-2 font-bold uppercase opacity-25 pt-sm">{{ fieldtypeLabel }}</span>
                </div>
            </template>
            <template slot="body">
                <field-settings v-ref=settings
                                :field.sync="field"
                                :fieldtype-config="fieldtypeConfig"
                                :fieldtypes="fieldtypes"
                                :root="isRootLevel">
                </field-settings>
            </template>
        </modal>
    </div>

</template>

<script>
import elementResizeDetectorMaker from "element-resize-detector"
import FieldSettings from '../FieldSettings.vue';
import WidthSelector from './WidthSelector.vue';
import ReplicatorSets from './Sets.vue';

const erd = elementResizeDetectorMaker({ strategy: "scroll" });

export default {

    components: {
        FieldSettings,
        WidthSelector,
        ReplicatorSets
    },

    props: {
        field: {},
        fieldtypes: {},
        section: {},
        isFirstField: {},
        isLastField: {},
        parentKey: {
            default: ''
        }
    },

    data() {
        return {
            handleModified: false,
            isEditing: false,
            isShowingGridFields: false,
            isShowingSets: false,
            isAddingGridField: false,
            displayFieldWidth: '100%',
            nameFieldWidth: '100%',
            width: null,
            height: null
        }
    },

    computed: {

        display() {
            return this.field.display;
        },

        name() {
            return this.field.name;
        },

        icon() {
            if (this.isMeta) {
                if (this.field.name === 'title') return 'text';
                if (this.field.name === 'slug') return 'location-pin';
                if (this.field.name === 'date') return 'calendar';
            }

            return _.find(this.fieldtypes, { name: this.field.type }).icon;
        },

        fieldKey() {
            return (this.parentKey == '') ? this.field.id : `${this.parentKey}-${this.field.id}`;
        },

        fieldtype() {
            return _.findWhere(this.fieldtypes, { name: this.field.type });
        },

        fieldtypeConfig() {
            return this.fieldtype.config;
        },

        fieldtypeLabel() {
            return this.fieldtype.label;
        },

        otherSections() {
            return _.filter(this.$parent.$parent.sections, section => {
                return section.id !== this.section.id;
            });
        },

        isRootLevel() {
            return this.$parent.isRootLevel;
        },

        isGridField() {
            return this.field.type === 'grid';
        },

        isReplicatorField() {
            return ['replicator', 'bard'].includes(this.field.type);
        },

        classes() {
            return [
                'section-field',
                `section-field--${this.parentKey}`,
                `w-full md:${tailwind_width_class(this.field.width)}`,
                {
                    'is-editing': this.isEditing,
                    'root-level-section-field': this.isRootLevel,
                    'is-first-field': this.isFirstField,
                    'is-last-field': this.isLastField,
                    'is-tiny': this.isTiny
                }
            ];
        },

        canBeLocalized() {
            return this.isRootLevel
                && Object.keys(Statamic.locales).length > 1
                && this.fieldtype.canBeLocalized;
        },

        isTiny() {
            return this.width < 400;
        }

    },

    watch: {

        display(val) {
            if (!this.handleModified) {
                this.field.name = this.$slugify(val, '_');
            }

            this.$nextTick(() => this.updateFieldWidths());
        },

        name(val) {
            this.updateFieldWidths();
        },

        isShowingGridFields(val) {
            if (!val) return;

            this.$nextTick(() =>  this.$refs.fields.updateFieldWidths());
        },

        isShowingSets(val) {
            if (!val) return;

            this.$nextTick(() => this.$refs.sets.updateFieldWidths());
        }

    },

    mounted() {
        erd.listenTo(this.$el, el => {
            this.width = el.offsetWidth
            this.height = el.offsetHeight
        });
        this.handleModified = !this.field.isNew || this.field.isMeta;
        this.updateFieldWidths();
    },

    methods: {

        focus() {
            this.$refs.display.select();
        },

        edit() {
            this.isEditing = true;
            this.$nextTick(() => this.$refs.settings.focus());
        },

        addField() {
            this.isAddingGridField = true;
            this.isShowingGridFields = true;
        },

        fieldSelectorClosed() {
            this.isAddingGridField = false;

            if (this.field.fields.length === 0) {
                this.isShowingGridFields = false;
            }
        },

        updateFieldWidths() {
            this.displayFieldWidth = this.$refs.fieldDisplaySizer.offsetWidth + 'px';
            this.nameFieldWidth = this.$refs.fieldNameSizer.offsetWidth + 'px';
        },

        addReplicatorSet() {
            this.isShowingSets = true;
            this.$refs.sets.add();
        }

    }

}
</script>
