<template>
    <tr :class="[sortableItemClass, { 'opacity-50': isExcessive, 'inset-ring-1 inset-ring-red': hasError }]">
        <td v-if="grid.isReorderable" class="drag-handle" :class="sortableHandleClass"></td>

        <FieldsProvider
            :fields="fields"
            :field-path-prefix="`${fieldPathPrefix}.${index}`"
            :meta-path-prefix="`${metaPathPrefix}.existing.${values._id}`"
        >
            <grid-cell v-for="(field, i) in fields" :key="field.handle" :field="field" />
        </FieldsProvider>

        <td class="grid-row-controls row-controls" v-if="!grid.isReadOnly && (canAddRows || canDelete)">
            <Dropdown v-if="canAddRows || canDelete" placement="left-start">
                <DropdownMenu>
                    <DropdownItem v-if="canAddRows" :text="__('Duplicate Row')" icon="duplicate" @click="$emit('duplicate', index)" />
                    <DropdownItem v-if="canDelete" :text="__('Delete Row')" icon="trash" variant="destructive" @click="$emit('removed', index, fields)" />
                </DropdownMenu>
            </Dropdown>
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
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import { Dropdown, DropdownMenu, DropdownItem } from '@statamic/cms/ui';

export default {
    components: { Dropdown, DropdownMenu, DropdownItem, FieldsProvider, GridCell },

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
        hasError: {
            type: Boolean,
            default: false,
        },
    },

    inject: ['grid', 'sortableItemClass', 'sortableHandleClass'],

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
    },
};
</script>
