<template>

    <node-view-wrapper>
        <div class="bard-set whitespace-normal my-6 rounded bg-white dark:bg-dark-500 border dark:border-dark-900 shadow-md"
            :class="{ 'border-blue-400 dark:border-dark-blue-100': selected || withinSelection, 'has-error': hasError }"
            :data-type="config.handle"
            contenteditable="false" @copy.stop @paste.stop @cut.stop
        >
            <div ref="content" hidden />
            <div class="replicator-set-header" :class="{'collapsed': collapsed, 'invalid': isInvalid }">
                <div class="item-move sortable-handle" data-drag-handle />
                <div class="flex items-center flex-1 p-2 replicator-set-header-inner cursor-pointer" :class="{'flex items-center': collapsed}" @click="toggleCollapsedState">
                    <label class="text-xs rtl:ml-2 ltr:mr-2">
                        <span v-if="isSetGroupVisible">
                            {{ __(setGroup.display) }}
                            <svg-icon name="micro/chevron-right" class="w-4" />
                        </span>
                        {{ display || config.handle }}
                    </label>
                    <div class="flex items-center" v-if="config.instructions && !collapsed">
                        <svg-icon name="micro/circle-help" class="text-gray-700 dark:text-dark-175 hover:text-gray-800 dark:hover:text-dark-100 h-3 w-3 text-xs" v-tooltip="{ content: $options.filters.markdown(__(config.instructions)), html:true }" />
                    </div>
                    <div v-show="collapsed" class="flex-1 min-w-0 w-1 rtl:pl-8 ltr:pr-8">
                        <div
                            v-html="previewText"
                            class="help-block mb-0 whitespace-nowrap overflow-hidden text-ellipsis" />
                    </div>
                </div>
                <div class="replicator-set-controls">
                    <toggle-fieldtype
                        handle="set-enabled"
                        class="toggle-sm rtl:ml-4 ltr:mr-4"
                        v-model="enabled"
                        v-tooltip.top="(enabled) ? __('Included in output') : __('Hidden from output')" />
                    <dropdown-list>
                        <dropdown-actions :actions="visibleFieldActions" v-if="visibleFieldActions.length" />
                        <div class="divider" />
                        <dropdown-item :text="__(collapsed ? __('Expand Set') : __('Collapse Set'))" @click="toggleCollapsedState" />
                        <dropdown-item :text="__('Duplicate Set')" @click="duplicate" />
                        <dropdown-item :text="__('Delete Set')" class="warning" @click="deleteNode" />
                    </dropdown-list>
                </div>
            </div>
            <div class="replicator-set-body publish-fields @container" v-show="!collapsed" v-if="index !== undefined">
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
                    :show-field-previews="showFieldPreviews"
                    @updated="updated(field.handle, $event)"
                    @meta-updated="metaUpdated(field.handle, $event)"
                    @focus="focused"
                    @blur="blurred"
                    @replicator-preview-updated="previewUpdated(field.handle, $event)"
                />
            </div>
        </div>
    </node-view-wrapper>

</template>

<script>
import { NodeViewWrapper } from '@tiptap/vue-2';
import SetField from '../replicator/Field.vue';
import ManagesPreviewText from '../replicator/ManagesPreviewText';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';
import HasFieldActions from '../../field-actions/HasFieldActions.js';
import DropdownActions from '../../field-actions/DropdownActions.vue';

export default {

    props: [
        'editor', // the editor instance
        'node', // access the current node
        'decorations', // an array of decorations
        'selected', // true when there is a NodeSelection at the current node view
        'extension', // access to the node extension, for example to get options
        'getPos', // get the document position of the current node
        'updateAttributes', // update attributes of the current node.
        'deleteNode', // delete the current node
    ],

    components: { NodeViewWrapper, SetField, DropdownActions },

    mixins: [
        ValidatesFieldConditions,
        ManagesPreviewText, 
        HasFieldActions,
    ],

    inject: ['bard', 'bardSets'],

    computed: {

        fields() {
            return this.config.fields;
        },

        display() {
            return __(this.config.display || this.values.type);
        },

        values() {
            return this.node.attrs.values;
        },

        meta() {
            return this.extension.options.bard.meta.existing[this.node.attrs.id] || {};
        },

        previews() {
            return this.extension.options.bard.meta.previews[this.node.attrs.id] || {};
        },

        collapsed() {
            return this.extension.options.bard.meta.collapsed.includes(this.node.attrs.id);
        },

        config() {
            return _.findWhere(this.setConfigs, { handle: this.values.type }) || {};
        },

        setConfigs() {
            return this.bard.setConfigs;
        },

        setGroup() {
            if (this.bardSets.length < 1) return null;

            return this.bardSets.find((group) => {
                return group.sets.filter((set) => set.handle === this.config.handle).length > 0;
            });
        },

        isSetGroupVisible() {
            return this.bardSets.length > 1 && this.setGroup?.display;
        },

        isReadOnly() {
            return this.bard.isReadOnly;
        },

        enabled: {
            get() {
                return this.node.attrs.enabled;
            },
            set(enabled) {
                return this.updateAttributes({ enabled })
            }
        },

        parentName() {
            return this.extension.options.bard.name;
        },

        index() {
            return this.extension.options.bard.setIndexes[this.node.attrs.id];
        },

        instructions() {
            return this.config.instructions ? markdown(__(this.config.instructions)) : null;
        },

        hasError() {
            return this.extension.options.bard.setsWithErrors.includes(this.index);
        },

        showFieldPreviews() {
            return this.extension.options.bard.config.previews;
        },

        isInvalid() {
            return Object.keys(this.config).length === 0;
        },

        decorationSpecs() {
            return Object.assign({}, ...this.decorations.map((decoration) => decoration.type.spec));
        },

        withinSelection() {
            return this.decorationSpecs.withinSelection;
        },

        fieldVm() {
            return this.extension.options.bard
        },

        fieldActionPayload() {
            return {
                vm: this,
                fieldVm: this.fieldVm,
                fieldPathPrefix: this.fieldVm.fieldPathPrefix || this.fieldVm.handle,
                index: this.index,
                values: this.values,
                config: this.config,
                meta: this.meta,
                update: (handle, value) => this.updated(handle, value),
                updateMeta: (handle, value) => this.metaUpdated(handle, value),
            };
        },

    },

    methods: {

        updated(handle, value) {
            let values = Object.assign({}, this.values);
            values.type = this.config.handle;
            values[handle] = value;
            this.updateAttributes({ values });
        },

        metaUpdated(handle, value) {
            let meta = clone(this.meta);
            meta[handle] = value;
            this.extension.options.bard.updateSetMeta(this.node.attrs.id, meta);
        },

        previewUpdated(handle, value) {
            this.extension.options.bard.updateSetPreviews(this.node.attrs.id, { ...this.previews, [handle]: value });
        },

        focused() {
            this.extension.options.bard.$emit('focus');
        },

        blurred() {
            // Bard should only blur if we focus somewhere outside of Bard entirely.
            // We use a timeout because activeElement only exists after the blur event.
            setTimeout(() => {
                const bard = this.extension.options.bard;
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
            this.extension.options.bard.collapseSet(this.node.attrs.id);
        },

        expand() {
            // this.$events.$emit('expanded', this.node.attrs.id);
            this.extension.options.bard.expandSet(this.node.attrs.id);
        },

        duplicate() {
            // this.$events.$emit('duplicated', this.node.attrs.id);
            this.extension.options.bard.duplicateSet(this.node.attrs.id, this.node.attrs, this.getPos() + this.node.nodeSize);
        },

        fieldPath(field) {
            let prefix = this.extension.options.bard.fieldPathPrefix || this.extension.options.bard.handle;
            return `${prefix}.${this.index}.attrs.values.${field.handle}`;
        },

    },

    updated() {
        // This is a workaround to avoid Firefox's inability to select inputs/textareas when the
        // parent element is set to draggable: https://bugzilla.mozilla.org/show_bug.cgi?id=739071
        this.$el.setAttribute('draggable', false);
    }
}
</script>
