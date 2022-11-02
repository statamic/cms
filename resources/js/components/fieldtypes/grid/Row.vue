<template>

    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive }]">
        <td class="drag-handle" :class="sortableHandleClass" v-if="grid.isReorderable"></td>
        <grid-cell
            v-for="(field, i) in fields"
            :show-inner="showField(field, fieldPath(field.handle))"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            :meta="meta[field.handle]"
            :index="i"
            :row-index="index"
            :grid-name="name"
            :errors="errors(field.handle)"
            :field-path="fieldPath(field.handle)"
            @updated="updated(field.handle, $event)"
            @meta-updated="metaUpdated(field.handle, $event)"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <td class="row-controls" v-if="!grid.isReadOnly && (canAddRows || canDelete)">
            <dropdown-list>
                <dropdown-item :text="__('Duplicate Row')" @click="$emit('duplicate', index)" v-if="canAddRows" />
                <dropdown-item v-if="canDelete" :text="__('Delete Row')" class="warning" @click="$emit('removed', index)" />
            </dropdown-list>
        </td>
    </tr>

</template>

<style scoped>
    .draggable-mirror {
        display: none;
    }
</style>

<script>
import GridCell from './Cell.vue';
import { ValidatesFieldConditions } from '../../field-conditions/FieldConditions.js';

export default {

    components: { GridCell },

    mixins: [ValidatesFieldConditions],

    props: {
        index: {
            type: Number,
            required: true
        },
        fields: {
            type: Array,
            required: true
        },
        values: {
            type: Object,
            required: true
        },
        meta: {
            type: Object,
            required: true
        },
        name: {
            type: String,
            required: true
        },
        fieldPathPrefix: {
            type: String
        },
        canDelete: {
            type: Boolean,
            default: true
        },
        canAddRows: {
            type: Boolean,
            default: true
        },
    },

    inject: [
        'grid',
        'sortableItemClass',
        'sortableHandleClass',
        'storeName',
    ],

    computed: {
        isExcessive() {
            const max = this.grid.config.max_rows;
            if (! max) return false;
            return this.index >= max;
        }
    },

    methods: {
        updated(handle, value) {
            let row = JSON.parse(JSON.stringify(this.values));
            row[handle] = value;
            this.$emit('updated', this.index, row);
        },

        metaUpdated(handle, value) {
            let meta = clone(this.meta);
            meta[handle] = value;
            this.$emit('meta-updated', meta);
        },

        fieldPath(handle) {
            return `${this.fieldPathPrefix}.${this.index}.${handle}`;
        },

        errors(handle) {
            const state = this.$store.state.publish[this.storeName];
            if (! state) return [];
            return state.errors[this.fieldPath(handle)] || [];
        },

    }

}
</script>
