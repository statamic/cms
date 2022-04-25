<template>

    <div class="table-fieldtype-container">
        <table class="table-fieldtype-table" v-if="rowCount">
            <thead>
                <tr>
                    <th class="grid-drag-handle-header" v-if="!isReadOnly"></th>
                    <th v-for="(column, index) in columnCount" :key="index">
                        <div class="flex items-center justify-between h-6">
                            <span class="column-count">{{ index + 1 }}</span>
                            <a v-show="canDeleteColumns" class="opacity-25 text-lg antialiased hover:opacity-75" @click="confirmDeleteColumn(index)" :aria-label="__('Delete Column')">
                                &times;
                            </a>
                        </div>
                    </th>
                    <th class="row-controls" v-if="canDeleteRows"></th>
                </tr>
            </thead>

            <sortable-list
                v-model="data"
                :vertical="true"
                item-class="sortable-row"
                handle-class="table-drag-handle"
                @dragstart="$emit('focus')"
                @dragend="$emit('blur')"
            >
                <tbody>
                    <tr class="sortable-row" v-for="(row, rowIndex) in data" :key="row._id">
                        <td class="table-drag-handle" v-if="!isReadOnly"></td>
                        <td v-for="(cell, cellIndex) in row.value.cells">
                            <input type="text" v-model="row.value.cells[cellIndex]" class="input-text" :readonly="isReadOnly" @focus="$emit('focus')" @blur="$emit('blur')" />
                        </td>
                        <td class="row-controls" v-if="canDeleteRows">
                            <button @click="confirmDeleteRow(rowIndex)" class="inline opacity-25 text-lg antialiased hover:opacity-75" :aria-label="__('Delete Row')">&times;</button>
                        </td>
                    </tr>
                </tbody>
            </sortable-list>
        </table>

        <button class="btn" @click="addRow" :disabled="atRowMax" v-if="canAddRows">
            {{ __('Add Row') }}
        </button>

        <button class="btn ml-1" @click="addColumn" :disabled="atColumnMax" v-if="canAddColumns">
            {{ __('Add Column') }}
        </button>

        <confirmation-modal
            v-if="deletingRow !== false"
            :title="__('Delete Row')"
            :bodyText="__('Are you sure you want to delete this row?')"
            :buttonText="__('Delete')"
            :danger="true"
            @confirm="deleteRow(deletingRow)"
            @cancel="deleteCancelled"
        >
        </confirmation-modal>

        <confirmation-modal
            v-if="deletingColumn !== false"
            :title="__('Delete Column')"
            :bodyText="__('Are you sure you want to delete this column?')"
            :buttonText="__('Delete')"
            :danger="true"
            @confirm="deleteColumn(deletingColumn)"
            @cancel="deleteCancelled"
        >
        </confirmation-modal>
    </div>

</template>

<script>
import { SortableList, SortableItem, SortableHelpers } from '../sortable/Sortable';
import SortableKeyValue from '../sortable/SortableKeyValue';

export default {

    mixins: [Fieldtype, SortableHelpers],

    components: {
        SortableList,
        SortableItem
    },

    data: function () {
        return {
            data: this.arrayToSortable(this.value || []),
            deletingRow: false,
            deletingColumn: false,
        }
    },

    watch: {
        data: {
            deep: true,
            handler (data) {
                this.updateDebounced(this.sortableToArray(data));
            }
        },

        value(value, oldValue) {
            if (JSON.stringify(value) == JSON.stringify(oldValue)) return;
            if (JSON.stringify(value) == JSON.stringify(this.sortableToArray(this.data))) return;
            this.data = this.arrayToSortable(value);
        }
    },

    computed: {
        maxRows() {
            return this.config.max_rows || null;
        },

        maxColumns() {
            return this.config.max_columns || null;
        },

        rowCount() {
            return this.data.length;
        },

        columnCount() {
            return data_get(this, 'data.0.value.cells.length', 0);
        },

        atRowMax() {
            return this.maxRows ? this.rowCount >= this.maxRows : false;
        },

        atColumnMax() {
            return this.maxColumns ? this.columnCount >= this.maxColumns : false;
        },

        canAddRows() {
            return !this.isReadOnly;
        },

        canDeleteRows() {
            return !this.isReadOnly;
        },

        canAddColumns() {
            return !this.isReadOnly && this.rowCount > 0;
        },

        canDeleteColumns() {
            return !this.isReadOnly && this.columnCount > 1;
        },

        replicatorPreview() {
            // Join all values with commas. Exclude any empties.
            return _(this.data)
                .map(row => row.value.cells.filter(cell => !!cell).join(', '))
                .filter(row => !!row).join(', ');
        }
    },

    methods: {
        addRow() {
            this.data.push(this.newSortableValue({
                'cells': new Array(this.columnCount || 1)
            }));
        },

        addColumn() {
            var rows = this.data.length;

            for (var i = 0; i < rows; i++) {
                this.data[i].value.cells.push('');
            }
        },

        confirmDeleteRow(index) {
            this.deletingRow = index;
        },

        confirmDeleteColumn(index) {
            this.deletingColumn = index;
        },

        deleteRow(index) {
            this.deletingRow = false;

            this.data.splice(index, 1);
        },

        deleteColumn(index) {
            this.deletingColumn = false;

            var rows = this.data.length;

            for (var i = 0; i < rows; i++) {
                this.data[i].value.cells.splice(index, 1);
            }
        },

        deleteCancelled() {
            this.deletingRow = false;
            this.deletingColumn = false;
        }
    }

}
</script>
