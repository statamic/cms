<script>
import Fuse from 'fuse.js';

export default {
    props: {
        columns: {
            required: true,
        },
        visibleColumns: {
            default() {
                return this.columns;
            }
        },
        rows: {
            type: Array,
            required: true,
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
                visibleColumns: this.visibleColumns,
                sortColumn: this.sort ? this.visibleColumns[0] : null,
                sortDirection: 'asc',
                rows: [],
                selections: this.selections,
                maxSelections: this.maxSelections,
            }
        }
    },

    computed: {

        filteredRows() {
            let rows = this.rows;
            rows = this.filterBySearch(rows);
            return this.sortRows(rows);
        }

    },

    watch: {

        filteredRows: {
            immediate: true,
            handler: function (rows) {
                this.sharedState.rows = rows;
            }
        },

        'sharedState.selections': function (selections) {
            this.$emit('selections-updated', selections);
        }

    },

    methods: {

        filterBySearch(rows) {
            if (! this.searchQuery) return rows;

            // TODO: Ensure instance respects updates to visibleColumns
            const fuse = new Fuse(rows, {
                findAllMatches: true,
                threshold: 0.1,
                minMatchCharLength: 2,
                keys: this.visibleColumns
            });

            return fuse.search(this.searchQuery);
        },

        sortRows(rows) {
            // If no column is selected, don't sort.
            if (! this.sharedState.sortColumn) return rows;

            rows = _.sortBy(rows, this.sharedState.sortColumn);

            if (this.sharedState.sortDirection === 'desc') {
                rows = rows.reverse();
            }

            return rows;
        }

    },

    render() {
        return this.$scopedSlots.default({ });
    }

}
</script>
