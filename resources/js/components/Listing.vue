<script>
import HasActions from './data-list/HasActions';
import HasFilters from './data-list/HasFilters';
import HasPagination from './data-list/HasPagination';
import HasPreferences from './data-list/HasPreferences';

export default {
    mixins: [HasActions, HasFilters, HasPagination, HasPreferences],

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
            visibleColumns: this.initialColumns.filter((column) => column.visible),
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            meta: null,
            pushQuery: false,
            popping: false,
        };
    },

    computed: {
        parameterMap() {
            return {
                sort: 'sortColumn',
                order: 'sortDirection',
                page: 'page',
                perPage: 'perPage',
                search: 'searchQuery',
                filters: 'activeFilterParameters',
                columns: 'visibleColumnParameters',
            };
        },

        parameters: {
            get() {
                const parameters = Object.fromEntries(
                    Object.entries(this.parameterMap)
                        .map(([key, prop]) => {
                            return [key, this[prop]];
                        })
                        .filter(([key, value]) => {
                            return value !== null && value !== undefined && value !== '';
                        }),
                );
                return {
                    ...parameters,
                    ...this.additionalParameters,
                };
            },
            set(value) {
                Object.entries(this.parameterMap).forEach(([key, prop]) => {
                    if (value.hasOwnProperty(key)) {
                        this[prop] = value[key];
                    }
                });
            },
        },

        activeFilterParameters: {
            get() {
                if (_.isEmpty(this.activeFilters)) {
                    return null;
                }
                return utf8btoa(JSON.stringify(this.activeFilters));
            },
            set(value) {
                this.activeFilters = JSON.parse(utf8atob(value));
            },
        },

        visibleColumnParameters: {
            get() {
                if (_.isEmpty(this.visibleColumns)) {
                    return null;
                }
                return this.visibleColumns.map((column) => column.field).join(',');
            },
            set(value) {
                this.visibleColumns = value
                    .split(',')
                    .map((field) => this.columns.find((column) => column.field === field));
            },
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
        this.autoApplyState();
        this.request();
    },

    mounted() {
        if (this.pushQuery) {
            window.history.replaceState({ parameters: this.parameters }, '');
            window.addEventListener('popstate', this.popState);
        }
    },

    beforeUnmount() {
        if (this.pushQuery) {
            window.removeEventListener('popstate', this.popState);
        }
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
                this.pushState();
            },
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(this.listingKey, loading);
            },
        },

        searchQuery(query) {
            this.sortColumn = null;
            this.sortDirection = null;
            this.resetPage();
            this.request();
            this.pushState();
        },
    },

    methods: {
        request() {
            if (!this.requestUrl) {
                this.loading = false;
                return;
            }

            this.loading = true;

            if (this.source) this.source.abort();
            this.source = new AbortController();

            this.$axios
                .get(this.requestUrl, {
                    params: this.parameters,
                    signal: this.source.signal,
                })
                .then((response) => {
                    this.columns = response.data.meta.columns;
                    this.activeFilterBadges = { ...response.data.meta.activeFilterBadges };
                    this.items = Object.values(response.data.data);
                    this.meta = response.data.meta;
                    if (this.shouldRequestFirstPage) return this.request();
                    this.loading = false;
                    this.initializing = false;
                    this.afterRequestCompleted(response);
                })
                .catch((e) => {
                    if (this.$axios.isCancel(e)) return;
                    this.loading = false;
                    this.initializing = false;
                    if (e.request && !e.response) return;
                    this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), {
                        duration: null,
                    });
                });
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

        popState(event) {
            if (!this.pushQuery || !event.state) {
                return;
            }
            this.popping = true;
            this.parameters = event.state.parameters;
            this.$nextTick(() => {
                this.popping = false;
            });
        },

        pushState() {
            if (!this.pushQuery || this.popping) {
                return;
            }
            const parameters = this.parameters;
            const keys = Object.keys(this.parameterMap);
            // This ensures no additionalParameters are added to the URL
            const searchParams = new URLSearchParams(
                Object.fromEntries(
                    keys.filter((key) => parameters.hasOwnProperty(key)).map((key) => [key, parameters[key]]),
                ),
            );
            window.history.pushState({ parameters }, '', '?' + searchParams.toString());
        },

        autoApplyState() {
            if (!this.pushQuery || !window.location.search) {
                return;
            }
            const searchParams = new URLSearchParams(window.location.search);
            const parameters = Object.fromEntries(searchParams.entries());
            this.popping = true;
            this.parameters = parameters;
            this.$nextTick(() => {
                this.popping = false;
            });
        },
    },
};
</script>
