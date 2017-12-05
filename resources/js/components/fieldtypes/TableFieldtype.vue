<template>
    <div class="table-field">
    	<table class="bordered-table" v-if="rowCount || columnCount">
    		<thead>
    			<tr>
    				<th v-for="column in columnCount">
    					<span class="column-count">{{ $index + 1 }}</span>
    					<span class="icon icon-cross delete-column" @click="deleteColumn($index)"></span>
    				</th>
    				<th class="row-controls"></th>
    			</tr>
    		</thead>
    		<tbody>
    			<tr v-for="row in data">
    				<td v-for="cell in row.cells" track-by="$index">
    					<input type="text" v-model="cell" class="form-control" />
    				</td>
    				<td class="row-controls">
    					<span class="icon icon-menu move drag-handle"></span>
    					<span class="icon icon-cross delete" v-on:click="deleteRow($index)"></span>
    				</td>
    			</tr>
    		</tbody>
    	</table>

    	<div class="btn-group">
    		<a class="btn btn-default" @click="addRow" v-if="canAddRows">
    			{{ translate_choice('cp.rows', 1) }} <i class="icon icon-plus icon-right"></i>
    		</a>
    		<a class="btn btn-default" @click="addColumn" v-if="canAddColumns">
    			{{ translate_choice('cp.columns', 1) }} <i class="icon icon-plus icon-right"></i>
    		</a>
    	</div>
    </div>

</template>

<script>
module.exports = {

    mixins: [Fieldtype],

    data: function () {
        return {
            max_rows: this.config.max_rows || null,
            max_columns: this.config.max_columns || null,
            autoBindChangeWatcher: false,
            sortableInitialized: false
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

        sortable() {
            if (this.sortableInitialized || this.data.length === 0) return;

            var self = this,
                start = '';

            $(this.$el).find('tbody').sortable({
                axis: "y",
                revert: 175,
                handle: '.drag-handle',
                placeholder: "table-row-placeholder",
                forcePlaceholderSize: true,

                start: function(e, ui) {
                    start = ui.item.index();
                    ui.placeholder.height(ui.item.height());
                },

                update: function(e, ui) {
                    var end  = ui.item.index(),
                        swap = self.data.splice(start, 1)[0];

                    self.data.splice(end, 0, swap);
                }
            });

            this.sortableInitialized = true;
        },

        destroySortable() {
            $(this.$el).find('tbody').sortable('destroy');
            this.sortableInitialized = false;
        },

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
                title: translate('cp.are_you_sure'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, function() {
                self.data.splice(index, 1);
            });
        },

        deleteColumn: function(index) {
            var self = this;

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                text: translate_choice('cp.confirm_delete_items', 1),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
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
    },

    ready: function() {
        if ( ! this.data) {
            this.data = [];
        }

        this.bindChangeWatcher();
        this.sortable();
    },

    watch: {

        data(data) {
            this.$nextTick(() => {
                if (this.data.length) {
                    this.sortable();
                } else {
                    this.destroySortable();
                }
            });
        }

    }
}
</script>
