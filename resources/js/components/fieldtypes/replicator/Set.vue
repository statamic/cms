<template>

    <div :class="sortableItemClass" class="replicator-set">

        <div class="replicator-set-header" :class="{ 'p-1': isReadOnly, 'collapsed': collapsed }">
            <div class="item-move sortable-handle" :class="sortableHandleClass" v-if="!isReadOnly"></div>
            <div class="flex-1 ml-1 flex items-center" @dblclick="toggleCollapsedState">
                <label v-text="config.display" class="text-xs"/>
                <div
                    v-if="config.instructions"
                    v-html="instructions"
                    class="help-block replicator-set-instructions" />
            </div>
            <div class="replicator-set-controls" v-if="!isReadOnly">
                <toggle-fieldtype handle="set-enabled" class="toggle-sm mr-2" @input="toggleEnabledState" :value="values.enabled" />
                <dropdown-list>
                    <dropdown-item :text="__(collapsed ? 'Expand Set' : 'Collapse Set')" @click="toggleCollapsedState" />
                    <dropdown-item :text="__('Delete Set')" class="warning" @click="destroy" />
                </dropdown-list>
            </div>
        </div>

        <div class="replicator-set-body" v-show="!collapsed">
            <set-field
                v-for="field in fields"
                v-show="showField(field)"
                :key="field.handle"
                :field="field"
                :meta="meta[field.handle]"
                :value="values[field.handle]"
                :parent-name="parentName"
                :set-index="index"
                :error-key="errorKey(field)"
                :read-only="isReadOnly"
                @updated="updated(field.handle, $event)"
                @meta-updated="metaUpdated(field.handle, $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            />
        </div>

        <slot name="picker" />

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
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {

    components: { SetField },

    mixins: [ValidatesFieldConditions],

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
        errorKeyPrefix: {
            type: String,
            required: true
        },
        sortableItemClass: {
            type: String
        },
        sortableHandleClass: {
            type: String
        },
        isReadOnly: Boolean,
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
        }

    },

    methods: {

        updated(handle, value) {
            let set = JSON.parse(JSON.stringify(this.values));
            set[handle] = value;
            this.$emit('updated', this.index, set);
        },

        metaUpdated(handle, value) {
            let meta = clone(this.meta);
            meta[handle] = value;
            this.$emit('meta-updated', meta);
        },

        destroy() {
            if (! confirm(__('Are you sure?'))) return;

            this.$emit('removed');
        },

        toggle() {
            this.isHidden ? this.expand() : this.collapse();
        },

        toggleEnabledState() {
            Vue.set(this.values, 'enabled', ! this.values.enabled);
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

        errorKey(field) {
            return `${this.errorKeyPrefix}.${this.index}.${field.handle}`;
        }

    }

}
</script>
