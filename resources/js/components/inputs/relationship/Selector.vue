<template>

    <div class="h-full bg-white">

        <div v-if="initializing" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            ref="dataList"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            :selections="selections"
            :max-selections="maxSelections"
            @selections-updated="selectionsUpdated"
        >
            <div slot-scope="{}" class="flex flex-col h-full">
                <div class="bg-white border-b flex items-center justify-between bg-grey-20">
                    <div class="p-2 flex flex-1 items-center">
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            @filter-changed="filterChanged"
                            @search-changed="searchChanged"
                            @reset="filtersReset"
                        />
                    </div>
                </div>

                <div class="flex-1 flex flex-col min-h-0">
                    <div class="flex flex-col h-full justify-start">
                        <div class="flex-1 overflow-scroll">
                            <data-list-table
                                :loading="loading"
                                :allow-bulk-actions="true"
                                :toggle-selection-on-row-click="true"
                                :type="type"
                                @sorted="sorted"
                                class="cursor-pointer"
                            >
                                <template slot="cell-title" slot-scope="{ row: entry }">
                                    <div class="flex items-center">
                                        <div v-if="entry.published !== undefined" class="little-dot mr-1" :class="getStatusClass(entry)" />
                                        {{ entry.title }}
                                    </div>
                                </template>
                                <template slot="cell-url" slot-scope="{ row: entry }">
                                    <span class="text-2xs">{{ entry.url }}</span>
                                </template>
                            </data-list-table>
                        </div>

                        <data-list-pagination
                            v-if="meta.last_page > 1"
                            class="border-t shadow-lg"
                            :resource-meta="meta"
                            :inline="true"
                            @page-selected="setPage" />

                        <div class="p-2 border-t flex items-center justify-between bg-grey-20">
                            <div class="text-sm text-grey-70"
                                v-text="hasMaxSelections
                                    ? __n(':count/:max selected', selections, { max: maxSelections })
                                    : __n(':count item selected|:count items selected', selections)" />

                            <div>
                                <button
                                    type="button"
                                    class="btn"
                                    @click="close">
                                    {{ __('Cancel') }}
                                </button>

                                <button
                                    v-if="! hasMaxSelections || maxSelections > 1"
                                    type="button"
                                    class="btn-primary ml-1"
                                    @click="select">
                                    {{ __('Select') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </data-list>
    </div>

</template>

<script>
import HasFilters from '../../data-list/HasFilters';

export default {

    mixins: [
        HasFilters,
    ],

    props: {
        filtersUrl: String,
        selectionsUrl: String,
        initialSelections: Array,
        initialSortColumn: String,
        initialSortDirection: String,
        maxSelections: Number,
        site: String,
        search: Boolean,
        type: String,
        exclusions: {
            type: Array,
            default: () => []
        },
        initialColumns: {
            type: Array,
            default: () => []
        }
    },

    data() {
        return {
            source: null,
            initializing: true,
            loading: true,
            items: [],
            meta: {},
            filters: [],
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            page: 1,
            selections: _.clone(this.initialSelections),
            columns: this.initialColumns,
            visibleColumns: this.initialColumns.filter(column => column.visible),
        }
    },

    computed: {

        parameters() {
            return {
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                site: this.site,
                exclusions: this.exclusions,
                filters: utf8btoa(JSON.stringify(this.activeFilters)),
                columns: this.visibleColumns.map(column => column.field).join(','),
            }
        },

        hasMaxSelections() {
            return (this.maxSelections === Infinity) ? false : Boolean(this.maxSelections);
        }

    },

    mounted() {
        this.getFilters().then(() => {
            this.autoApplyFilters(this.filters);
            this.initialRequest();
        });
    },

    watch: {

        parameters: {
            deep: true,
            handler(after, before) {
                if (this.initializing) return;
                if (JSON.stringify(before) === JSON.stringify(after)) return;
                this.request();
            }
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading('relationship-selector-listing', loading);
            }
        },

        searchQuery(query) {
            this.sortColumn = null;
            this.sortDirection = null;
            this.page = 1;

            this.request();
        },

        selections() {
            if (this.maxSelections === 1 && this.selections.length === 1) {
                this.select();
            }
        },

    },

    methods: {

        getFilters() {
            if (!this.filtersUrl) return Promise.resolve();

            return this.$axios.get(this.filtersUrl).then(response => {
                this.filters = response.data;
            });
        },

        initialRequest() {
            return this.request().then(() => {
                if (this.search) this.$refs.filters.$refs.search.focus();
            });
        },

        request() {
            this.loading = true;

            if (this.source) this.source.cancel();
            this.source = this.$axios.CancelToken.source();

            const params = {...this.parameters, ...{
                search: this.searchQuery,
            }};

            return this.$axios.get(this.selectionsUrl, { params, cancelToken: this.source.token }).then(response => {
                this.columns = response.data.meta.columns;
                this.items = response.data.data;
                this.meta = response.data.meta;
                this.activeFilterBadges = {...response.data.meta.activeFilterBadges};
                this.loading = false;
                this.initializing = false;
            }).catch(e => {
                if (this.$axios.isCancel(e)) return;
                this.loading = false;
                this.initializing = false;
                this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), { duration: null });
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        setPage(page) {
            this.page = page;
        },

        select() {
            this.$emit('selected', this.selections);
            this.close()
        },

        close() {
            this.$emit('closed');
        },

        selectionsUpdated(selections) {
            this.selections = selections;
        },

        getStatusClass(entry) {
            if (entry.published && entry.private) {
                return 'bg-transparent border border-grey-60';
            } else if (entry.published) {
                return 'bg-green';
            } else {
                return 'bg-grey-40';
            }
        }

    }

}
</script>
