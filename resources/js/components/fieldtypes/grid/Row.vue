<template>
    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive }]">
        <td class="drag-handle" :class="sortableHandleClass" v-if="grid.isReorderable"></td>

        <FieldsProvider
            :fields="fields"
            :field-path-prefix="`${fieldPathPrefix}.${index}`"
            :meta-path-prefix="`${metaPathPrefix}.existing.${values._id}`"
        >
            <grid-cell v-for="(field, i) in fields" :key="field.handle" :field="field" />
        </FieldsProvider>

        <td class="grid-row-controls row-controls" v-if="!grid.isReadOnly && (canAddRows || canDelete)">
            <dropdown-list>
                <dropdown-item :text="__('Duplicate Row')" @click="$emit('duplicate', index)" v-if="canAddRows" />
                <dropdown-item
                    v-if="canDelete"
                    :text="__('Delete Row')"
                    class="warning"
                    @click="$emit('removed', index)"
                />
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
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';

export default {
    components: { FieldsProvider, GridCell },

    mixins: [ValidatesFieldConditions],

    props: {
        index: {
            type: Number,
            required: true,
        },
        fields: {
            type: Array,
            required: true,
        },
        values: {
            type: Object,
            required: true,
        },
        meta: {
            type: Object,
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        fieldPathPrefix: {
            type: String,
        },
        metaPathPrefix: {
            type: String,
        },
        canDelete: {
            type: Boolean,
            default: true,
        },
        canAddRows: {
            type: Boolean,
            default: true,
        },
    },

    inject: ['grid', 'sortableItemClass', 'sortableHandleClass', 'store'],

    data() {
        return {
            extraValues: {},
        };
    },

    computed: {
        isExcessive() {
            const max = this.grid.config.max_rows;
            if (!max) return false;
            return this.index >= max;
        },
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
            const state = this.store;
            if (!state) return [];
            return state.errors[this.fieldPath(handle)] || [];
        },
    },
};
</script>
