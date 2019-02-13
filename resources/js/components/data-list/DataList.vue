<script>
import Fuse from 'fuse.js';

export default {
    props: {
        columns: {
            required: true,
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

    created() {
        this.sharedState.sortColumn = this.sortColumn || (this.sort ? this.visibleColumns[0] : null);
    },

    methods: {

        filterBySearch(rows) {
            if (!this.search || !this.searchQuery) return rows;

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
            if (! this.sort) return rows;

            // If no column is selected, don't sort.
            if (! this.sharedState.sortColumn) return rows;

            rows = _.sortBy(rows, this.sharedState.sortColumn);

            if (this.sharedState.sortDirection === 'desc') {
                rows = rows.reverse();
            }

            return rows;
        },

    },

    render() {
        return this.$scopedSlots.default({ rows: this.filteredRows });
    }

}
</script>
