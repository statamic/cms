<template>
    <div>

        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :rows="entries"
            :columns="columns"
            :search="false"
            :search-query="searchQuery"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ }">
                <div class="card p-0">
                    <div class="data-list-header">
                        <data-list-toggle-all ref="toggleAll" />
                        <data-list-search v-model="searchQuery" />
                        <data-list-filters
                            :filters="filters"
                            :active-filters="activeFilters"
                            :per-page="perPage"
                            @filters-changed="filtersChanged"
                            @per-page-changed="perPageChanged" />
                        <data-list-column-picker @change="updateColumns" />
                    </div>
                    <data-list-bulk-actions
                        :url="actionUrl"
                        :actions="actions"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <data-list-table :loading="loading" :allow-bulk-actions="true" @sorted="sorted">
                        <template slot="cell-title" slot-scope="{ row: entry }">
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="[entry.published ? 'bg-green' : 'bg-yellow-dark']" />
                                <a :href="entry.edit_url">{{ entry.title }}</a>
                            </div>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: entry }">
                            <span class="font-mono text-2xs">{{ entry.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: entry, index }">
                            <dropdown-list>
                                <div class="dropdown-menu">
                                    <div class="li"><a :href="entry.permalink">View</a></div>
                                    <div class="li"><a :href="entry.edit_url">Edit</a></div>
                                    <div class="li divider" />
                                    <data-list-inline-actions
                                        :item="entry.id"
                                        :url="actionUrl"
                                        :actions="actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </div>
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>

                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    @page-selected="page = $event"
                />
            </div>
        </data-list>

    </div>
</template>

<script>
import axios from 'axios';

export default {

    props: {
        collection: String,
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
            entries: [],
            columns: [],
            sortColumn: this.initialSortColumn,
            sortDirection: this.initialSortDirection,
            meta: null,
            page: 1,
            perPage: 25, // TODO: Should come from the controller, or a config.
            searchQuery: '',
            activeFilters: {},
        }
    },


    computed: {

        parameters() {
            return {
                group: this.group,
                sort: this.sortColumn,
                order: this.sortDirection,
                page: this.page,
                perPage: this.perPage,
                search: this.searchQuery,
                filters: btoa(JSON.stringify(this.activeFilters))
            }
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
                this.$progress.loading('entries', loading);
            }
        }

    },

    methods: {

        request() {
            this.loading = true;
            const url = cp_url(`collections/${this.collection}/entries`);

            axios.get(url, { params: this.parameters }).then(response => {
                this.columns = response.data.meta.columns.map(column => column.field);
                this.sortColumn = response.data.meta.sortColumn;
                this.activeFilters = {...response.data.meta.filters};
                this.entries = response.data.data;
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
