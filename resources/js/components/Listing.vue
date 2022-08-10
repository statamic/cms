<script>
import HasActions from './data-list/HasActions';
import HasFilters from './data-list/HasFilters';
import HasPagination from './data-list/HasPagination';
import HasPreferences from './data-list/HasPreferences';

export default {

    mixins: [
        HasActions,
        HasFilters,
        HasPagination,
        HasPreferences,
    ],

    props: {
        initialSortColumn: String,
        initialSortDirection: String,
        initialColumns: {
            type: Array,
            default: () => [],
        },
        filters: Array,
        actionUrl: String,
    },

    data() {
        return {
            source: null,
            initializing: true,
            loading: true,
            items: [],
            columns: this.initialColumns,
            visibleColumns: this.initialColumns.filter(column => column.visible),
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            meta: null,
        }
    },

    computed: {

        parameters() {
            return Object.assign({
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                perPage: this.perPage,
                search: this.searchQuery,
                filters: this.activeFilterParameters,
                columns: this.visibleColumns.map(column => column.field).join(','),
            }, this.additionalParameters);
        },

        activeFilterParameters() {
            return utf8btoa(JSON.stringify(this.activeFilters));
        },

        additionalParameters() {
            return {};
        },

        shouldRequestFirstPage() {
            if (this.page > 1 && this.items.length === 0) {
                this.page = 1;
                return true;
            }

            return false;
        },

    },

    created() {
        this.autoApplyFilters(this.filters);
        this.request();
    },

    watch: {

        parameters: {
            deep: true,
            handler(after, before) {
                // A change to the search query would trigger both watchers.
                // We only want the searchQuery one to kick in.
                if (before.search !== after.search) return;

                if (JSON.stringify(before) === JSON.stringify(after)) return;
                this.request();
            }
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(this.listingKey, loading);
            }
        },

        searchQuery(query) {
            this.sortColumn = null;
            this.sortDirection = null;
            this.resetPage();
            this.request();
        }

    },

    methods: {

        request() {
            if (! this.requestUrl) {
                this.loading = false;
                return;
            }

            this.loading = true;

            if (this.source) this.source.cancel();
            this.source = this.$axios.CancelToken.source();

            this.$axios.get(this.requestUrl, {
                params: this.parameters,
                cancelToken: this.source.token
            }).then(response => {
                this.columns = response.data.meta.columns;
                this.activeFilterBadges = {...response.data.meta.activeFilterBadges};
                this.items = Object.values(response.data.data);
                this.meta = response.data.meta;
                if (this.shouldRequestFirstPage) return this.request();
                this.loading = false;
                this.initializing = false;
                this.afterRequestCompleted();
            }).catch(e => {
                if (this.$axios.isCancel(e)) return;
                this.loading = false;
                this.initializing = false;
                this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), { duration: null });
            })
        },

        afterRequestCompleted(response) {
            //
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        removeRow(row) {
            let id = row.id;
            let i = _.indexOf(this.rows, _.findWhere(this.rows, { id }));
            this.rows.splice(i, 1);
            if (this.rows.length === 0) location.reload();
        },

    }

}
</script>
