<template>

    <div :class="sortableItemClass">
        <slot name="picker" />
        <div class="replicator-set" :class="{ 'has-error': this.hasError }" :data-type="config.handle">

            <div class="replicator-set-header" :class="{ 'p-2': isReadOnly, 'collapsed': collapsed, 'invalid': isInvalid }">
                <div class="item-move sortable-handle" :class="sortableHandleClass" v-if="!isReadOnly"></div>
                <div class="flex items-center flex-1 p-2 replicator-set-header-inner cursor-pointer" :class="{'flex items-center': collapsed}" @click="toggleCollapsedState">
                    <label class="text-xs rtl:ml-2 ltr:mr-2 cursor-pointer">
                        <span v-if="isSetGroupVisible">
                            {{ __(setGroup.display) }}
                            <svg-icon name="micro/chevron-right" class="w-4" />
                        </span>
                        {{ display || config.handle }}
                    </label>
                    <div class="flex items-center" v-if="config.instructions && !collapsed">
                        <svg-icon name="micro/circle-help" class="text-gray-700 hover:text-gray-800 h-3 w-3 text-xs" v-tooltip="{ content: $options.filters.markdown(__(config.instructions)), html:true }" />
                    </div>
                    <div v-show="collapsed" class="flex-1 min-w-0 w-1 rtl:pl-8 ltr:pr-8">
                        <div
                            v-html="previewText"
                            class="help-block mb-0 whitespace-nowrap overflow-hidden text-ellipsis" />
                    </div>
                </div>
                <div class="replicator-set-controls" v-if="!isReadOnly">
                    <toggle-fieldtype
                        handle="set-enabled"
                        class="toggle-sm rtl:ml-2 ltr:mr-2"
                        @input="toggleEnabledState"
                        :value="values.enabled"
                        v-tooltip.top="(values.enabled) ? __('Included in output') : __('Hidden from output')" />
                    <dropdown-list>
                        <dropdown-actions :actions="fieldActions" v-if="fieldActions.length" />
                        <div class="divider" />
                        <dropdown-item :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))" @click="toggleCollapsedState" />
                        <dropdown-item :text="__('Duplicate Set')" @click="duplicate" v-if="canAddSet" />
                        <dropdown-item :text="__('Delete Set')" class="warning" @click="destroy" />
                    </dropdown-list>
                </div>
            </div>

            <div class="replicator-set-body publish-fields @container" v-show="!collapsed">
                <set-field
                    v-for="field in fields"
                    v-show="showField(field, fieldPath(field))"
                    :key="field.handle"
                    :field="field"
                    :meta="meta[field.handle]"
                    :value="values[field.handle]"
                    :parent-name="parentName"
                    :set-index="index"
                    :field-path="fieldPath(field)"
                    :read-only="isReadOnly"
                    :show-field-previews="showFieldPreviews"
                    @updated="updated(field.handle, $event)"
                    @meta-updated="metaUpdated(field.handle, $event)"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                    @replicator-preview-updated="previewUpdated(field.handle, $event)"
                />
            </div>
        </div>
    </div>

</template>

<style scoped>
    .draggable-mirror {
        position: relative;
        z-index: 1000;
    }
    .draggable-source--is-dragging {
        opacity: 0.5;
    }
</style>

<script>
import SetField from './Field.vue';
import ManagesPreviewText from './ManagesPreviewText';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';
import HasFieldActions from '../../field-actions/HasFieldActions.js';
import DropdownActions from '../../field-actions/DropdownActions.vue';

export default {

    components: { SetField, DropdownActions },

    mixins: [
        ValidatesFieldConditions,
        ManagesPreviewText,
        HasFieldActions,
    ],

    inject: ['replicatorSets'],

    props: {
        config: {
            type: Object,
            required: true
        },
        meta: {
            type: Object,
            required: true
        },
        index: {
            type: Number,
            required: true
        },
        collapsed: {
            type: Boolean,
            default: false
        },
        values: {
            type: Object,
            required: true
        },
        parentName: {
            type: String,
            required: true
        },
        fieldPathPrefix: {
            type: String,
            required: true
        },
        hasError: {
            type: Boolean,
            default: false
        },
        sortableItemClass: {
            type: String
        },
        sortableHandleClass: {
            type: String
        },
        canAddSet: {
            type: Boolean,
            default: true
        },
        isReadOnly: Boolean,
        previews: Object,
        showFieldPreviews: {
            type: Boolean
        }
    },

    data() {
        return {
            fieldPreviews: this.previews,
        }
    },

    computed: {

        fields() {
            return this.config.fields;
        },

        display() {
            return __(this.config.display) || this.values.type;
        },

        instructions() {
            return this.config.instructions ? markdown(__(this.config.instructions)) : null;
        },

        setGroup() {
            if (this.replicatorSets.length < 1) return null;

            return this.replicatorSets.find((group) => {
                return group.sets.filter((set) => set.handle === this.config.handle).length > 0;
            });
        },

        hasMultipleFields() {
            return this.fields.length > 1;
        },

        isSetGroupVisible() {
            return this.replicatorSets.length > 1 && this.setGroup?.display;
        },

        isHidden() {
            return this.values['#hidden'] === true;
        },

        isInvalid() {
            return Object.keys(this.config).length === 0;
        },

        fieldVm() {
            let vm = this;
            while (vm !== vm.$root) {
                if (vm.$options.name === 'replicator-fieldtype') return vm;
                vm = vm.$parent;
            }
        },

        fieldActionPayload() {
            return {
                vm: this,
                fieldVm: this.fieldVm,
                fieldPathPrefix: this.fieldPathPrefix,
                index: this.index,
                values: this.values,
                config: this.config,
                meta: this.meta,
                update: (handle, value) => this.updated(handle, value),
                updateMeta: (handle, value) => this.metaUpdated(handle, value),
                isReadOnly: this.isReadOnly,
            };
        },

    },

    methods: {

        updated(handle, value) {
            this.$emit('updated', this.index, {...this.values, [handle]: value });
        },

        metaUpdated(handle, value) {
            this.$emit('meta-updated', { ...this.meta, [handle]: value });
        },

        previewUpdated(handle, value) {
            this.$emit('previews-updated', this.fieldPreviews = { ...this.fieldPreviews, [handle]: value });
        },

        destroy() {
            if (! confirm(__('Are you sure?'))) return;

            this.$emit('removed');
        },

        toggle() {
            this.isHidden ? this.expand() : this.collapse();
        },

        toggleEnabledState() {
            this.updated('enabled', ! this.values.enabled);
        },

        toggleCollapsedState() {
            if (this.collapsed) {
                this.expand();
            } else {
                this.collapse();
            }
        },

        collapse() {
            this.$emit('collapsed');
        },

        expand() {
            this.$emit('expanded');
        },

        duplicate() {
            this.$emit('duplicated');
        },

        fieldPath(field) {
            return `${this.fieldPathPrefix}.${this.index}.${field.handle}`;
        },

    }

}
</script>