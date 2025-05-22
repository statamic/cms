<script>
import fuzzysort from 'fuzzysort';
import { sortBy } from 'lodash-es';

export default {
    emits: ['selections-updated', 'visible-columns-updated'],
    props: {
        columns: { type: Array, default: () => [] },
        rows: { type: Array, required: true },
        searchQuery: { type: String, default: '' },
        selections: { type: Array, default: () => [] },
        maxSelections: { type: Number },
        sort: { type: Boolean, default: true },
        sortColumn: { type: String },
        sortDirection: { type: String, default: 'asc' },
    },
    provide() {
        return {
            sharedState: this.sharedState,
        };
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
            },
        };
    },

    computed: {
        filteredRows() {
            let rows = this.rows;
            rows = this.filterBySearch(rows);
            return this.sortRows(rows);
        },

        visibleColumns() {
            return this.sharedState.columns.filter((column) => column.visible);
        },

        searchableColumns() {
            return this.visibleColumns.length
                ? this.visibleColumns.map((column) => column.field)
                : Object.keys(rows[0]);
        },
    },

    watch: {
        filteredRows: {
            immediate: true,
            handler: function (rows) {
                this.sharedState.rows = rows;
            },
        },

        selections(selections) {
            this.sharedState.selections = selections;
        },

        'sharedState.selections': {
            immediate: true,
            deep: true,
            handler: function (selections) {
                console.log('selections updated!', selections);
                this.$emit('selections-updated', selections);
            },
        },

        columns(columns) {
            this.sharedState.columns = columns;
        },

        sortColumn(column) {
            this.sharedState.sortColumn = column;
        },

        visibleColumns(columns) {
            this.$emit('visible-columns-updated', columns);
        },
    },

    created() {
        this.setInitialSortColumn();

        this.$events.$on('clear-selections', this.clearSelections);
    },

    unmounted() {
        this.$events.$off('clear-selections', this.clearSelections);
    },

    methods: {
        setInitialSortColumn() {
            const columns = this.sharedState.columns;

            if (columns.length === 0) return;

            let firstVisibleColumn = this.visibleColumns[0];
            firstVisibleColumn = firstVisibleColumn ? firstVisibleColumn.field : columns[0].field;
            this.sharedState.sortColumn = this.sortColumn || (this.sort ? firstVisibleColumn : null);
        },

        filterBySearch(rows) {
            if (!this.searchQuery) return rows;

            return fuzzysort
                .go(this.searchQuery, rows, {
                    all: true,
                    keys: this.searchableColumns,
                })
                .map((result) => result.obj);
        },

        sortRows(rows) {
            if (!this.sort) return rows;

            // If no column is selected, don't sort.
            if (!this.sharedState.sortColumn) return rows;

            rows = sortBy(rows, this.sharedState.sortColumn);

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
        return this.$slots.default({
            rows: this.filteredRows,
            hasSelections: this.sharedState.selections.length > 0,
        })[0];
    },
};
</script>
