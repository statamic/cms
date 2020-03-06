<template>
    <table class="data-table" :class="{ 'opacity-50': loading }">
        <thead v-if="visibleColumns.length > 1">
            <tr>
                <th class="checkbox-column" v-if="allowBulkActions || reorderable">
                    <data-list-toggle-all ref="toggleAll" v-tooltip="__('Select All')"/>
                </th>
                <th
                    v-for="column in visibleColumns"
                    :key="column.field"
                    :class="{
                        'current-column': sharedState.sortColumn === column.field,
                        'sortable-column': column.sortable === true,
                        'cursor-not-allowed': !sortable,
                    }"
                    @click.prevent="changeSortColumn(column.field)"
                >
                    <span v-text="column.label" />
                    <svg v-if="sharedState.sortColumn === column.field" :class="sharedState.sortDirection" height="8" width="8" viewBox="0 0 10 6.5">
                        <path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor"/>
                    </svg>
                </th>
                <th class="actions-column">
                    <button class="btn btn-sm px-sm py-sm -ml-sm cursor-pointer" v-tooltip="__('Customize Columns')">
                        <svg-icon name="settings-horizontal" class="w-4" />
                    </button>
                </th>
            </tr>
        </thead>
        <sortable-list
            :value="rows"
            :vertical="true"
            item-class="sortable-row"
            handle-class="table-drag-handle"
            @input="$emit('reordered', $event)"
        >
        <tbody>
            <slot name="tbody-start" />
            <tr v-for="(row, index) in rows" :key="row.id" class="sortable-row outline-none">
                <td class="table-drag-handle" v-if="reorderable"></td>
                <td class="checkbox-column" v-if="allowBulkActions && !reorderable">
                    <input
                        v-if="!reorderable"
                        type="checkbox"
                        :value="row.id"
                        v-model="sharedState.selections"
                        :disabled="reachedSelectionLimit && !sharedState.selections.includes(row.id)"
                        :id="`checkbox-${row.id}`"
                    />
                </td>
                <td v-for="column in visibleColumns" :key="column.field" @click="rowClicked(row)">
                    <slot
                        :name="`cell-${column.field}`"
                        :value="row[column.value || column.field]"
                        :values="row"
                        :row="row"
                        :index="actualIndex(row)"
                        :display-index="index"
                        :checkbox-id="`checkbox-${row.id}`"
                    >
                        <table-field :handle="column.field" :value="row[column.value || column.field]" :values="row" :fieldtype="column.fieldtype" :key="column.field" />
                    </slot>
                </td>
                <td class="actions-column">
                    <slot
                        name="actions"
                        :row="row"
                        :index="actualIndex(row)"
                        :display-index="index"
                    ></slot>
                </td>
            </tr>
        </tbody>
        </sortable-list>
    </table>
</template>

<script>
import TableField from './TableField.vue';
import SortableList from '../sortable/SortableList.vue';

export default {

    components: {
        TableField,
        SortableList,
    },

    props: {
        loading: {
            type: Boolean,
            default: false
        },
        allowBulkActions: {
            default: false,
            type: Boolean
        },
        toggleSelectionOnRowClick: {
            type: Boolean,
            default: false
        },
        sortable: {
            type: Boolean,
            default: true
        },
        reorderable: {
            type: Boolean,
            default: false
        },
    },

    inject: ['sharedState'],

    computed: {

        rows: {
            get() {
                return this.sharedState.rows;
            },
            set(rows) {
                this.sharedState.rows = rows;
            }
        },

        reachedSelectionLimit() {
            return this.sharedState.selections.length === this.sharedState.maxSelections;
        },

        visibleColumns() {
            const columns = this.sharedState.columns.filter(column => column.visible);

            return columns.length ? columns : this.sharedState.columns;
        },

        sortableColumns() {
            return this.sharedState.columns
                .filter(column => column.sortable)
                .map(column => column.field);
        }

    },

    methods: {

        changeSortColumn(column) {
            if (!this.sortable) return;

            if (! this.sortableColumns.includes(column)) {
                return;
            }

            this.sharedState.currentPage = 1;
            if (this.sharedState.sortColumn === column) this.swapSortDirection();
            this.sharedState.sortColumn = column;
            this.$emit('sorted', this.sharedState.sortColumn, this.sharedState.sortDirection);
        },

        swapSortDirection() {
            this.sharedState.currentPage = 1;
            this.sharedState.sortDirection = this.sharedState.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        actualIndex(row) {
            return _.findIndex(this.sharedState.originalRows, row);
        },

        rowClicked(row, i) {
            if (this.toggleSelectionOnRowClick) {
                this.toggleSelection(row.id);
            }
        },

        toggleSelection(id) {
            const i = this.sharedState.selections.indexOf(id);

            if (i != -1) {
                this.sharedState.selections.splice(i, 1);
            } else if (! this.reachedSelectionLimit) {
                this.sharedState.selections.push(id);
            }
        }

    }
}
</script>
