<script>
import Fuse from 'fuse.js';

export default {
    props: {
        columns: {
            type: Array,
            default: () => []
        },
        rows: {
            type: Array,
            required: true,
        },
        search: {
            type: Boolean,
            default: true
        },
        searchQuery: {
            type: String,
            default: ''
        },
        selections: {
            type: Array,
            default: () => []
        },
        maxSelections: {
            type: Number
        },
        sort: {
            type: Boolean,
            default: true
        },
        sortColumn: String,
        sortDirection: {
            type: String,
            default: 'asc'
        }
    },
    provide() {
        return {
            sharedState: this.sharedState
        }
    },
    data() {
        return {
            sharedState: {
                searchQuery: this.searchQuery,
                columns: this.columns,
                sortColumn: null,
                sortDirection: this.sortDirection,
                rows: [],
                originalRows: this.rows,
                selections: this.selections,
                maxSelections: this.maxSelections,
            }
        }
    },

    computed: {

        filteredRows() {
            return this.sortRows(this.rows);
        },

        visibleColumns() {
            const columns = this.sharedState.columns;
            return columns.filter(col => col.visible);
        }

    },

    watch: {

        filteredRows: {
            immediate: true,
            handler: function (rows) {
                this.sharedState.rows = rows;
            }
        },

        selections(selections) {
            this.sharedState.selections = selections;
        },

        'sharedState.selections': function (selections) {
            this.$emit('selections-updated', selections);
        },

        columns(columns) {
            this.sharedState.columns = columns;
        },

        sortColumn(column) {
            this.sharedState.sortColumn = column;
        }

    },

    created() {
        this.setInitialSortColumn();

        this.$events.$on('clear-selections', this.clearSelections);
    },

    methods: {

        setInitialSortColumn() {
            const columns = this.sharedState.columns;

            if (columns.length === 0) return;

            let firstVisibleColumn = this.visibleColumns[0];
            firstVisibleColumn = firstVisibleColumn ? firstVisibleColumn.field : columns[0].field;
            this.sharedState.sortColumn = this.sortColumn || (this.sort ? firstVisibleColumn : null);
        },

        sortRows(rows) {
            if (! this.sort) return rows;

            // If no column is selected, don't sort.
            if (! this.sharedState.sortColumn) return rows;

            rows = _.sortBy(rows, this.sharedState.sortColumn);

            if (this.sharedState.sortDirection === 'desc') {
                rows = rows.reverse();
            }

            return rows;
        },

        clearSelections() {
            this.sharedState.selections = [];
        },

    },

    render() {
        return this.$scopedSlots.default({
            rows: this.filteredRows,
            hasSelections: this.sharedState.selections.length > 0,
        });
    }

}
</script>
