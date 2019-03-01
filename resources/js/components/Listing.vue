<script>
import axios from 'axios';
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
        perPage: {
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
        }

    },

    methods: {

        request() {
            this.loading = true;

            axios.get(this.requestUrl, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns;
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

        perPageChanged(perPage) {
            this.perPage = perPage;
            this.page = 1;
        }

    }

}
</script>
