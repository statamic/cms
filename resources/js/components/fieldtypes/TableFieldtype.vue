<template>
    <div class="table-field">
    	<table class="bordered-table" v-if="rowCount || columnCount">
    		<thead>
    			<tr>
    				<th v-for="(column, $index) in columnCount" :key="$index">
    					<span class="column-count">{{ $index + 1 }}</span>
    					<span class="icon icon-cross delete-column" @click="deleteColumn($index)"></span>
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

    	<div class="btn-group">
    		<a class="btn btn-default" @click="addRow" v-if="canAddRows">
    			{{ __('Row') }} <i class="icon icon-plus icon-right"></i>
    		</a>
    		<a class="btn btn-default" @click="addColumn" v-if="canAddColumns">
    			{{ __('Column') }} <i class="icon icon-plus icon-right"></i>
    		</a>
    	</div>
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
            max_rows: this.config.max_rows || null,
            max_columns: this.config.max_columns || null,
        }
    },

    computed: {
    	columnCount: function() {
            if (! this.data) {
                return 0;
            }

            if (this.data[0]) {
                return this.data[0].cells.length;
            }

            return 0;
    	},

        rowCount: function() {
            if (! this.data) {
                return 0;
            }

            if (this.data.length) {
                return this.data.length;
            }

            return 0;
        },

        canAddRows: function() {
            if (this.max_rows) {
                return this.rowCount < this.max_rows;
            }

            return true;
        },

        canAddColumns: function() {
            if (this.rowCount || this.columnCount) {

                if (this.max_columns) {
                    return this.columnCount < this.max_columns;
                }

                return true;
            }

            return false;
        }
    },

    methods: {
    	addRow: function() {
            // If there are no columns, we will add one when we add a row.
            var count = (this.columnCount === 0) ? 1 : this.columnCount;

            this.data.push({
                cells: new Array(count)
            });
    	},

    	addColumn: function() {
            var rows = this.data.length;

            for (var i = 0; i < rows; i++) {
                this.data[i].cells.push('');
            }
    	},

        deleteRow: function(index) {
            var self = this;

            swal({
                type: 'warning',
                title: __('Are you sure?'),
                confirmButtonText: __('Yes, I\'m sure'),
                cancelButtonText: __('Cancel'),
                showCancelButton: true
            }, function() {
                self.data.splice(index, 1);
            });
        },

        deleteColumn: function(index) {
            var self = this;

            swal({
                type: 'warning',
                title: __('Are you sure?'),
                text: __n('cp.confirm_delete_items', 1),
                confirmButtonText: __('Yes I\'m sure'),
                cancelButtonText: __('Cancel'),
                showCancelButton: true
            }, function() {
                var rows = self.data.length;

                for (var i = 0; i < rows; i++) {
                    self.data[i].cells.splice(index, 1);
                }
            });
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
