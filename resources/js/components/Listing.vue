<script>
let source;

import HasActions from './data-list/HasActions';
import HasFilters from './data-list/HasFilters';

export default {

    mixins: [
        HasActions,
        HasFilters,
    ],

    props: {
        initialSortColumn: String,
        initialSortDirection: String,
        initialPerPage: {
            type: Number,
            default: 25
        }
    },

    data() {
        return {
            initializing: true,
            loading: true,
            items: [],
            columns: [],
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            page: 1,
            perPage: this.initialPerPage,
            meta: null,
            searchQuery: '',
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

        parameters(after, before) {
            // A change to the search query would trigger both watchers.
            // We only want the searchQuery one to kick in.
            if (before.search !== after.search) return;

            if (JSON.stringify(before) === JSON.stringify(after)) return;
            this.request();
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(this.listingKey, loading);
            }
        },

        searchQuery(query) {
            this.page = 1;
            this.sortColumn = null;
            this.sortDirection = null;
            this.request();
        }

    },

    methods: {

        request() {
            this.loading = true;

            if (source) source.cancel();
            source = this.$axios.CancelToken.source();

            this.$axios.get(this.requestUrl, {
                params: this.parameters,
                cancelToken: source.token
            }).then(response => {
                this.columns = response.data.meta.columns;
                this.sortColumn = response.data.meta.sortColumn;
                this.activeFilters = {...response.data.meta.filters};
                this.items = response.data.data;
                this.meta = response.data.meta;
                this.loading = false;
                this.initializing = false;
            }).catch(e => {
                if (this.$axios.isCancel(e)) return;
                this.loading = false;
                this.initializing = false;
                this.$notify.error(e.response ? e.response.data.message : __('Something went wrong'));
            })
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        perPageChanged(perPage) {
            this.perPage = perPage;
            this.page = 1;
        }

    }

}
</script>
