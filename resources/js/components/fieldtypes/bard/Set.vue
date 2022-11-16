<template>

    <div class="bard-set whitespace-normal my-3 rounded bg-white border shadow"
        :class="{ 'border-blue-lighter': selected, 'has-error': hasError }"
        contenteditable="false" @copy.stop @paste.stop @cut.stop
    >
        <div ref="content" hidden />
        <div class="replicator-set-header" :class="{'collapsed': collapsed, 'invalid': isInvalid }">
            <div class="item-move sortable-handle" data-drag-handle />
            <div class="flex-1 p-1 replicator-set-header-inner" :class="{'flex items-center': collapsed}" @dblclick="toggleCollapsedState">
                <label v-text="display || config.handle" class="text-xs whitespace-no-wrap mr-1"/>
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
            <div class="replicator-set-controls">
                <toggle-fieldtype
                    handle="set-enabled"
                    class="toggle-sm mr-2"
                    v-model="enabled"
                    v-tooltip.top="(enabled) ? __('Included in output') : __('Hidden from output')" />
                <dropdown-list class="-mt-sm">
                    <dropdown-item :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))" @click="toggleCollapsedState" />
                    <dropdown-item :text="__('Duplicate Set')" @click="duplicate" />
                    <dropdown-item :text="__('Delete Set')" class="warning" @click="destroy" />
                </dropdown-list>
            </div>
        </div>
        <div class="replicator-set-body" v-show="!collapsed" v-if="index !== undefined">
            <set-field
                v-for="field in fields"
                v-show="showField(field, fieldPath(field))"
                :key="field.handle"
                :field="field"
                :value="values[field.handle]"
                :meta="meta[field.handle]"
                :parent-name="parentName"
                :set-index="index"
                :field-path="fieldPath(field)"
                :read-only="isReadOnly"
                @updated="updated(field.handle, $event)"
                @meta-updated="metaUpdated(field.handle, $event)"
                @focus="focused"
                @blur="blurred"
                @replicator-preview-updated="previewUpdated(field.handle, $event)"
            />
        </div>
    </div>

</template>

<script>
import SetField from '../replicator/Field.vue';
import ManagesPreviewText from '../replicator/ManagesPreviewText';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {

    props: [
        'node', // Prosemirror Node Object
        'view', // Prosemirror EditorView Object
        'getPos', // function allowing the view to find its position
        'updateAttrs', // function to update attributes defined in `schema`
        'editable', // global editor prop whether the content can be edited
        'options', // array of extension options
        'selected', // whether its selected,
    ],

    components: { SetField },

    mixins: [ValidatesFieldConditions, ManagesPreviewText],

    inject: ['setConfigs', 'isReadOnly'],

    computed: {

        fields() {
            return this.config.fields;
        },

        display() {
            return this.config.display || this.values.type;
        },

        values() {
            return this.node.attrs.values;
        },

        meta() {
            return this.options.bard.meta.existing[this.node.attrs.id];
        },

        previews() {
            return this.options.bard.meta.previews[this.node.attrs.id];
        },

        collapsed() {
            return this.options.bard.meta.collapsed.includes(this.node.attrs.id);
        },

        config() {
            return _.findWhere(this.setConfigs, { handle: this.values.type }) || {};
        },

        enabled: {
            get() {
                return this.node.attrs.enabled;
            },
            set(enabled) {
                return this.updateAttrs({ enabled })
            }
        },

        parentName() {
            return this.options.bard.name;
        },

        index() {
            return this.options.bard.setIndexes[this.node.attrs.id];
        },

        instructions() {
            return this.config.instructions ? markdown(this.config.instructions) : null;
        },

        hasError() {
            return this.options.bard.setsWithErrors.includes(this.index);
        },

        showFieldPreviews() {
            return this.options.bard.config.previews;
        },

        isInvalid() {
            return Object.keys(this.config).length === 0;
        }

    },

    methods: {

        updated(handle, value) {
            let values = Object.assign({}, this.values);
            values.type = this.config.handle;
            values[handle] = value;
            this.updateAttrs({ values });
        },

        metaUpdated(handle, value) {
            let meta = clone(this.meta);
            meta[handle] = value;
            this.options.bard.updateSetMeta(this.node.attrs.id, meta);
        },

        previewUpdated(handle, value) {
            let previews = clone(this.previews);
            previews[handle] = value;
            this.options.bard.updateSetPreviews(this.node.attrs.id, previews);
        },

        destroy() {
            let tr = this.view.state.tr;
            let pos = this.getPos();
            tr.delete(pos, pos + this.node.nodeSize);
            this.view.dispatch(tr);
        },

        focused() {
            this.options.bard.$emit('focus');
        },

        blurred() {
            // Bard should only blur if we focus somewhere outside of Bard entirely.
            // We use a timeout because activeElement only exists after the blur event.
            setTimeout(() => {
                const bard = this.options.bard;
                if (!bard.$el.contains(document.activeElement)) bard.$emit('blur');
            }, 1);
        },

         toggleCollapsedState() {
            if (this.collapsed) {
                this.expand();
            } else {
                this.collapse();
            }
        },

        collapse() {
            // this.$events.$emit('collapsed', this.node.attrs.id);
            this.options.bard.collapseSet(this.node.attrs.id);
        },

        expand() {
            // this.$events.$emit('expanded', this.node.attrs.id);
            this.options.bard.expandSet(this.node.attrs.id);
        },

        duplicate() {
            // this.$events.$emit('duplicated', this.node.attrs.id);
            this.options.bard.duplicateSet(this.node.attrs.id, this.node.attrs, this.getPos() + this.node.nodeSize);
        },

        fieldPath(field) {
            let prefix = this.options.bard.fieldPathPrefix || this.options.bard.handle;
            return `${prefix}.${this.index}.attrs.values.${field.handle}`;
        },

    }
}
</script>
