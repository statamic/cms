<template>

    <div class="table-field">
        <table class="bordered-table" v-if="rowCount">
            <thead>
                <tr>
                    <th v-for="(column, index) in columnCount" :key="index">
                        <div class="flex">
                            <span class="column-count text-center flex-grow">{{ index + 1 }}</span>
                            <span v-if="canDeleteColumns" class="icon icon-cross delete-column" @click="confirmDeleteColumn(index)"></span>
                        </div>
                    </th>
                    <th class="row-controls"></th>
                </tr>
            </thead>

            <sortable-list
                v-model="data"
                :vertical="true"
                item-class="sortable-row"
                handle-class="sortable-handle"
            >
                <tbody>
                    <tr class="sortable-row" v-for="(row, rowIndex) in data" :key="row._id">
                        <td v-for="(cell, cellIndex) in row.cells">
                            <input type="text" v-model="row['cells'][cellIndex]" class="input-text" />
                        </td>
                        <td class="row-controls">
                            <span class="icon icon-menu move sortable-handle"></span>
                            <span class="icon icon-cross delete" @click="confirmDeleteRow(rowIndex)"></span>
                        </td>
                    </tr>
                </tbody>
            </sortable-list>
        </table>

        <button class="btn" @click="addRow" :disabled="atRowMax">
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
import uniqid from 'uniqid';
import { SortableList, SortableItem } from '../sortable/Sortable';

export default {

    mixins: [Fieldtype],

    components: {
        SortableList,
        SortableItem
    },

    data: function () {
        return {
            data: [],
            deletingRow: false,
            deletingColumn: false,
        }
    },

    watch: {
        data: {
            deep: true,
            handler (data) {
                this.update(data);
            }
        }
    },

    created() {
        // Assign each row a unique id that Vue can use as a v-for key.
        this.data = JSON.parse(JSON.stringify(this.value || []))
            .map(row => Object.assign(row, { _id: uniqid() }));
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
            return data_get(this, 'data.0.cells.length', 0);
        },

        atRowMax() {
            return this.maxRows ? this.rowCount >= this.maxRows : false;
        },

        atColumnMax() {
            return this.maxColumns ? this.columnCount >= this.maxColumns : false;
        },

        canAddColumns() {
            return this.rowCount > 0;
        },

        canDeleteColumns() {
            return this.columnCount > 1;
        }
    },

    methods: {
        addRow() {
            this.data.push({
                _id: uniqid(),
                cells: new Array(this.columnCount || 1)
            });
        },

        addColumn() {
            var rows = this.data.length;

            for (var i = 0; i < rows; i++) {
                this.data[i].cells.push('');
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
                this.data[i].cells.splice(index, 1);
            }
        },

        deleteCancelled() {
            this.deletingRow = false;
            this.deletingColumn = false;
        },

        getReplicatorPreviewText() {
            // Join all values with commas. Exclude any empties.
            return _(this.data)
                .map(row => row.cells.filter(cell => !!cell).join(', '))
                .filter(row => !!row).join(', ');
        }
    }

}
</script>
