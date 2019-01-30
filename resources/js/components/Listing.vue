<script>
import axios from 'axios';

export default {

    props: {
        initialSortColumn: String,
        initialSortDirection: String,
        filters: Array,
        actions: Array,
        actionUrl: String
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
            perPage: 25, // TODO: Should come from the controller, or a config.
            meta: null,
            searchQuery: '',
            activeFilters: {},
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
            if (JSON.stringify(before) === JSON.stringify(after)) return;
            this.request();
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(this.listingKey, loading);
            }
        }

    },

    methods: {

        request() {
            this.loading = true;

            axios.get(this.requestUrl, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns.map(column => column.field);
                this.sortColumn = response.data.meta.sortColumn;
                this.activeFilters = {...response.data.meta.filters};
                this.items = response.data.data;
                this.meta = response.data.meta;
                this.loading = false;
                this.initializing = false;
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        updateColumns() {
            //
        },

        perPageChanged(perPage) {
            this.perPage = perPage;
            this.page = 1;
        },

        filtersChanged(filters) {
            this.activeFilters = filters;
            this.$refs.toggleAll.uncheckAllItems();
        },

        actionStarted() {
            this.loading = true;
        },

        actionCompleted() {
            this.request();
        }

    }

}
</script>
