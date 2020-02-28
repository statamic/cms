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
        filters: Array,
    },

    data() {
        return {
            source: null,
            initializing: true,
            loading: true,
            items: [],
            columns: [],
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            meta: null,
            searchQuery: '',
        }
    },

    computed: {

        userOnlyParameters() { // must never be set from server response
            return Object.assign({
                page: this.page,
                perPage: this.perPage,
                filters: btoa(JSON.stringify(this.activeFilters)),
            }, this.additionalParameters);
        },

        additionalParameters() {
            return {};
        }

    },

    created() {
        this.request();
    },

    watch: {

        userOnlyParameters: {
            deep: true,
            handler(after, before) {
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
            this.pageReset();
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
                params: {
                    ...this.userOnlyParameters,
                    search: this.searchQuery,
                    sort: this.sortColumn,
                    order: this.sortDirection,
                },
                cancelToken: this.source.token
            }).then(response => {
                this.columns = response.data.meta.columns;
                this.sortColumn = response.data.meta.sortColumn;
                this.sortDirection = response.data.meta.sortDirection;
                this.activeFilters = {...response.data.meta.filters};
                this.items = Object.values(response.data.data);
                this.meta = response.data.meta;
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
            if (column === this.sortColumn && direction === this.sortDirection) return;
            this.sortColumn = column;
            this.sortDirection = direction;
            this.pageReset();
            this.request();
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
