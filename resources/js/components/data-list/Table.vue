<template>
    <table
        :data-size="relativeColumnsSize"
        :class="{ 'select-none': shifting, 'data-table': !unstyled }"
        data-table
        ref="table"
        tabindex="0"
        @keydown.shift="shiftDown"
        @keyup="clearShift"
    >
        <thead v-if="allowBulkActions || visibleColumns.length > 1">
            <tr>
                <th
                    v-if="allowBulkActions || reorderable"
                    :class="{ 'checkbox-column': !reorderable, 'handle-column': reorderable }"
                >
                    <data-list-toggle-all ref="toggleAll" v-if="allowBulkActions && !singleSelect" />
                </th>
                <th v-for="column in visibleColumns" :key="column.field" :class="{ 'pe-8 text-end': column.numeric }">
                    <span v-if="!column.sortable" v-text="__(column.label)" />
                    <Button
                        v-else
                        :text="__(column.label)"
                        :icon-append="isCurrentSortColumn(column) ? 'up-down' : null"
                        size="sm"
                        variant="ghost"
                        class="-mt-2 -mb-1 text-sm! font-medium! text-gray-800! dark:text-gray-400!"
                        @click.prevent="changeSortColumn(column.field)"
                    />
                </th>
                <th class="type-column" v-if="type">
                    <template v-if="type === 'entries'">{{ __('Collection') }}</template>
                    <template v-if="type === 'terms'">{{ __('Taxonomy') }}</template>
                </th>
                <th class="actions-column" />
            </tr>
        </thead>
        <sortable-list
            :model-value="rows"
            :vertical="true"
            :mirror="false"
            item-class="sortable-row"
            handle-class="table-drag-handle"
            @update:model-value="$emit('reordered', $event)"
        >
            <tbody>
                <slot name="tbody-start" />
                <tr
                    v-for="(row, index) in rows"
                    :key="row.id"
                    class="sortable-row outline-hidden"
                    :class="{ 'row-selected': sharedState.selections.includes(row.id) }"
                >
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
                    <td
                        v-for="column in visibleColumns"
                        :key="column.field"
                        :width="column.width"
                        :class="{ 'pr-8 text-end': column.numeric }"
                    >
                        <slot
                            :name="`cell-${column.field}`"
                            :value="row[column.value || column.field]"
                            :values="row"
                            :row="row"
                            :index="actualIndex(row)"
                            :display-index="index"
                            :checkbox-id="`checkbox-${row.id}`"
                        >
                            <table-field
                                :handle="column.field"
                                :value="row[column.value || column.field]"
                                :values="row"
                                :fieldtype="column.fieldtype"
                                :key="column.field"
                            />
                        </slot>
                    </td>
                    <td class="type-column" v-if="type">
                        <Badge
                            size="sm"
                            v-if="type === 'entries' || type === 'terms'"
                            :label="type === 'entries' ? __(row.collection.title) : __(row.taxonomy.title)"
                        />
                    </td>
                    <td class="actions-column">
                        <slot name="actions" :row="row" :index="actualIndex(row)" :display-index="index"></slot>
                    </td>
                </tr>
            </tbody>
        </sortable-list>
    </table>
</template>

<script>
import TableField from './TableField.vue';
import SortableList from '../sortable/SortableList.vue';
import { Button, Badge } from '@statamic/ui';

export default {
    components: {
        TableField,
        SortableList,
        Button,
        Badge,
    },

    data() {
        return {
            shifting: false,
            lastItemClicked: null,
        };
    },

    props: {
        loading: { type: Boolean, default: false },
        allowBulkActions: { type: Boolean, default: false },
        toggleSelectionOnRowClick: { type: Boolean, default: false },
        sortable: { type: Boolean, default: true },
        reorderable: { type: Boolean, default: false },
        type: { type: String },
        unstyled: { type: Boolean, default: false },
    },

    inject: ['sharedState'],

    computed: {
        rows: {
            get() {
                return this.sharedState.rows;
            },
            set(rows) {
                this.sharedState.rows = rows;
            },
        },

        reachedSelectionLimit() {
            return this.sharedState.selections.length === this.sharedState.maxSelections;
        },

        relativeColumnsSize() {
            if (this.visibleColumns.length <= 4) return 'sm';
            if (this.visibleColumns.length <= 8) return 'md';
            if (this.visibleColumns.length >= 12) return 'lx';

            return 'xl';
        },

        singleSelect() {
            return this.sharedState.maxSelections === 1;
        },

        visibleColumns() {
            const columns = this.sharedState.columns.filter((column) => column.visible);

            return columns.length ? columns : this.sharedState.columns;
        },

        sortableColumns() {
            return this.sharedState.columns.filter((column) => column.sortable).map((column) => column.field);
        },

        isCurrentSortColumn() {
            return (column) => this.sharedState.sortColumn === column.field;
        },
    },

    methods: {
        changeSortColumn(column) {
            if (!this.sortable) return;

            if (!this.sortableColumns.includes(column)) {
                return;
            }

            // If sorting by the same column, flip the direction
            if (this.sharedState.sortColumn === column) {
                this.swapSortDirection();

                // Always start sorting by asc unless column is a date field
            } else if (this.getFieldtype(column) !== 'date') {
                this.sharedState.sortDirection = 'asc';
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
            let field = this.sharedState.columns.find(function (field) {
                return columnName === field.field;
            });

            return field.fieldtype;
        },

        actualIndex(row) {
            return this.sharedState.originalRows.findIndex((r) => r === row);
        },

        selectRange(from, to) {
            for (var i = from; i <= to; i++) {
                let row = this.sharedState.rows[i].id;
                if (!this.sharedState.selections.includes(row) && !this.reachedSelectionLimit) {
                    this.sharedState.selections.push(row);
                }
            }
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

            if (!this.reachedSelectionLimit) {
                this.sharedState.selections.push(id);
            }
        },

        shiftDown() {
            this.shifting = true;
        },

        clearShift() {
            this.shifting = false;
        },

        checkboxClicked(row, index, $event) {
            if ($event.shiftKey && this.lastItemClicked !== null) {
                this.$refs.table.focus();
                this.selectRange(Math.min(this.lastItemClicked, index), Math.max(this.lastItemClicked, index));
            } else {
                this.toggleSelection(row.id, index);
            }

            if ($event.target.checked) {
                this.lastItemClicked = index;
            }
        },
    },
};
</script>
