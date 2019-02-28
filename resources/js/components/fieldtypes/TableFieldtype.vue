<template>
    <div class="table-field">
    	<table class="bordered-table" v-if="rowCount">
    		<thead>
    			<tr>
    				<th v-for="(column, index) in columnCount" :key="index">
    					<span class="column-count">{{ index + 1 }}</span>
    					<span v-if="canDeleteColumns" class="icon icon-cross delete-column" @click="deleteColumn(index)"></span>
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
                    <tr class="sortable-row" v-for="(row, rowIndex) in data" :key="rowIndex">
                        <td v-for="(cell, cellIndex) in row.cells" :key="cellIndex">
                            <input type="text" v-model="row['cells'][cellIndex]" class="form-control" :key="`${rowIndex}-${cellIndex}`"/>
                        </td>
                        <td class="row-controls">
                            <span class="icon icon-menu move sortable-handle"></span>
                            <span class="icon icon-cross delete" @click="deleteRow(rowIndex)"></span>
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
    </div>

</template>

<script>
import { SortableList, SortableItem } from '../sortable/Sortable';

export default {

    mixins: [Fieldtype],

    components: {
        SortableList,
        SortableItem
    },

    data: function () {
        return {
            data: JSON.parse(JSON.stringify(this.value || [])),
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
            return data_get(this, 'data.0.cells.length', 0);
    	},

        atRowMax() {
            return this.maxRows ? this.rowCount === this.maxRows : false;
        },

        atColumnMax() {
            return this.maxColumns ? this.columnCount === this.maxColumns : false;
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
                cells: new Array(this.columnCount || 1)
            });
    	},

    	addColumn() {
            var rows = this.data.length;

            for (var i = 0; i < rows; i++) {
                this.data[i].cells.push('');
            }
    	},

        deleteRow(index) {
            swal({
                type: 'warning',
                title: __('Are you sure?'),
                confirmButtonText: __('Yes, I\'m sure'),
                cancelButtonText: __('Cancel'),
                showCancelButton: true
            }, function () {
                this.data.splice(index, 1);
            }.bind(this));
        },

        deleteColumn(index) {
            swal({
                type: 'warning',
                title: __('Are you sure?'),
                text: __n('cp.confirm_delete_items', 1),
                confirmButtonText: __('Yes I\'m sure'),
                cancelButtonText: __('Cancel'),
                showCancelButton: true
            }, function() {
                var rows = this.data.length;

                for (var i = 0; i < rows; i++) {
                    this.data[i].cells.splice(index, 1);
                }
            }.bind(this));
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
