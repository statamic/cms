<template>

    <div :class="classes" class="replicator-set">

        <slot name="picker" />

        <div class="replicator-set-header" :class="{ 'p-1': isReadOnly, 'collapsed': collapsed, 'invalid': isInvalid }">
            <div class="item-move sortable-handle" :class="sortableHandleClass" v-if="!isReadOnly"></div>
            <div class="flex-1 p-1 replicator-set-header-inner cursor-pointer" :class="{'flex items-center': collapsed}" @click="toggleCollapsedState">
                <label v-text="display || config.handle" class="text-xs whitespace-no-wrap mr-1 cursor-pointer"/>
                <div
                    v-if="config.instructions"
                    v-show="!collapsed"
                    v-html="instructions"
                    class="help-block mt-1 -mb-1" />

                <div v-show="collapsed" class="flex-1 min-w-0 w-1 pr-4">
                    <div
                        v-html="previewText"
                        class="help-block mb-0 whitespace-no-wrap overflow-hidden text-overflow-ellipsis" />
                </div>
            </div>
            <div class="replicator-set-controls" v-if="!isReadOnly">
                <toggle-fieldtype
                    handle="set-enabled"
                    class="toggle-sm mr-2"
                    @input="toggleEnabledState"
                    :value="values.enabled"
                    v-tooltip.top="(values.enabled) ? __('Included in output') : __('Hidden from output')" />
                <dropdown-list class="-mt-sm">
                    <dropdown-item :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))" @click="toggleCollapsedState" />
                    <dropdown-item :text="__('Duplicate Set')" @click="duplicate" v-if="canAddSet" />
                    <dropdown-item :text="__('Delete Set')" class="warning" @click="destroy" />
                </dropdown-list>
            </div>
        </div>

        <div class="replicator-set-body" v-show="!collapsed">
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
                @updated="updated(field.handle, $event)"
                @meta-updated="metaUpdated(field.handle, $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
                @replicator-preview-updated="previewUpdated(field.handle, $event)"
            />
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

export default {

    components: { SetField },

    mixins: [ValidatesFieldConditions, ManagesPreviewText],

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

    computed: {

        fields() {
            return this.config.fields;
        },

        display() {
            return this.config.display || this.values.type;
        },

        instructions() {
            return this.config.instructions ? markdown(this.config.instructions) : null;
        },

        hasMultipleFields() {
            return this.fields.length > 1;
        },

        isHidden() {
            return this.values['#hidden'] === true;
        },

        isInvalid() {
            return Object.keys(this.config).length === 0;
        },

        classes() {
            return [
                this.sortableItemClass,
                { 'has-error': this.hasError }
            ];
        }

    },

    methods: {

        updated(handle, value) {
            this.$emit('updated', this.index, {...this.values, [handle]: value });
        },

        metaUpdated(handle, value) {
            this.$emit('meta-updated', { ...this.meta, [handle]: value });
        },

        previewUpdated(handle, value) {
            setTimeout(() => {
                this.$emit('previews-updated', { ...this.previews, [handle]: value });
            }, 0);
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
