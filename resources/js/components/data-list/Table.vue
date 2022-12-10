<template>
    <table ref="table" tabindex="0" class="data-table" :class="{ 'opacity-50': loading, 'select-none' : shifting }" @keydown.shift="shiftDown" @keyup="clearShift">
        <thead v-if="allowBulkActions || allowColumnPicker || visibleColumns.length > 1">
            <tr>
                <th class="checkbox-column" v-if="allowBulkActions || reorderable">
                    <data-list-toggle-all ref="toggleAll" v-if="allowBulkActions && !singleSelect" />
                </th>
                <th
                    v-for="column in visibleColumns"
                    :key="column.field"
                    :class="{
                        'current-column': sharedState.sortColumn === column.field,
                        'sortable-column': column.sortable === true,
                        'cursor-not-allowed': !sortable,
                        'text-right pr-4': column.numeric,
                    }"
                    @click.prevent="changeSortColumn(column.field)"
                >
                    <span v-text="column.label" />
                    <svg v-if="sharedState.sortColumn === column.field" :class="sharedState.sortDirection" height="8" width="8" viewBox="0 0 10 6.5">
                        <path d="M9.9,1.4L5,6.4L0,1.4L1.4,0L5,3.5L8.5,0L9.9,1.4z" fill="currentColor"/>
                    </svg>
                </th>
                <th class="type-column" v-if="type">
                    <template v-if="type === 'entries'">{{ __('Collection') }}</template>
                    <template v-if="type === 'terms'">{{ __('Taxonomy') }}</template>
                </th>
                <th class="actions-column">
                    <data-list-column-picker :preferences-key="columnPreferencesKey" v-if="allowColumnPicker" />
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
            <tr v-for="(row, index) in rows" :key="row.id" class="sortable-row outline-none" :class="{'row-selected': sharedState.selections.includes(row.id)}">
                <td class="table-drag-handle" v-if="reorderable"></td>
                <td class="checkbox-column" v-if="allowBulkActions && !reorderable">
                    <input
                        v-if="!reorderable"
                        type="checkbox"
                        :value="row.id"
                        :checked="isSelected(row.id)"
                        :disabled="reachedSelectionLimit && !singleSelect && !isSelected(row.id)"
                        :id="`checkbox-${row.id}`"
                        @click="checkboxClicked(row, index, $event)"
                    />
                </td>
                <td v-for="column in visibleColumns" :key="column.field" @click="rowClicked(row, index, $event)" :width="column.width" :class="{'text-right pr-4': column.numeric}">
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
                <td class="type-column" v-if="type">
                    <span v-if="type === 'entries' || type === 'terms'" class="rounded px-sm py-px text-2xs uppercase bg-grey-20 text-grey">
                        <template v-if="type === 'entries'">{{ row.collection.title }}</template>
                        <template v-if="type === 'terms'">{{ row.taxonomy.title }}</template>
                    </span>
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

    data() {
        return {
            shifting: false,
            lastItemClicked: null
        }
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
        allowColumnPicker: {
            type: Boolean,
            default: false
        },
        columnPreferencesKey: {
            type: String,
        },
        type: {
            type: String
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

        singleSelect() {
            return this.sharedState.maxSelections === 1;
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

            // If sorting by the same column, flip the direction
            if (this.sharedState.sortColumn === column) {
                this.swapSortDirection();

            // Always start sorting by asc unless column is a date field
            } else if (this.getFieldtype(column) !== 'date') {
                this.sharedState.sortDirection = 'asc'
            }

            this.sharedState.currentPage = 1;
            this.sharedState.sortColumn = column;
            this.$emit('sorted', this.sharedState.sortColumn, this.sharedState.sortDirection);
        },

        swapSortDirection() {
            this.sharedState.currentPage = 1;
            this.sharedState.sortDirection = this.sharedState.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        getFieldtype(columnName) {
            let field = _.find(this.sharedState.columns, function(field) {
                return columnName === field.field
            })

            return field.fieldtype
        },

        actualIndex(row) {
            return _.findIndex(this.sharedState.originalRows, row);
        },

        rowClicked(row, index, $event) {
            if ($event.shiftKey && this.lastItemClicked !== null) {
                this.selectRange(
                    Math.min(this.lastItemClicked, index),
                    Math.max(this.lastItemClicked, index)
                );
            } else if (this.toggleSelectionOnRowClick) {
                this.toggleSelection(row.id, index);
            }
            this.lastItemClicked = index;
        },

        selectRange(from, to) {
            for (var i = from; i <= to; i++ ) {
                let row = this.sharedState.rows[i].id;
                if (! this.sharedState.selections.includes(row) && ! this.reachedSelectionLimit) {
                    this.sharedState.selections.push(row);
                }
            };
        },

        isSelected(id) {
            return this.sharedState.selections.includes(id);
        },

        toggleSelection(id) {
            const i = this.sharedState.selections.indexOf(id);

            if (i > -1) {
                this.sharedState.selections.splice(i, 1);

                return;
            }

            if (this.singleSelect) {
                this.sharedState.selections.pop();
            }

            if (! this.reachedSelectionLimit) {
                this.sharedState.selections.push(id);
            }
        },

        shiftDown() {
            this.shifting = true
        },

        clearShift() {
            this.shifting = false
        },

        checkboxClicked(row, index, $event) {
            this.$refs.table.focus();
            if ($event.shiftKey && this.lastItemClicked !== null) {
                this.selectRange(
                    Math.min(this.lastItemClicked, index),
                    Math.max(this.lastItemClicked, index)
                );
            } else {
                this.toggleSelection(row.id, index)
            }

            if ($event.target.checked) {
                this.lastItemClicked = index
            }
        }
    },
}
</script>
