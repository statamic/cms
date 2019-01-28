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
                        <data-list-toggle-all />
                        <data-list-search v-model="searchQuery" />
                        <data-list-bulk-actions>
                            <div slot-scope="{ selections, hasSelections }" class="flex items-center" v-if="hasSelections">
                                <button class="btn ml-1" @click="bulkDelete(selections)">Delete</button>
                                <button class="btn ml-1" @click="bulkUnpublish(selections)">Unpublish</button>
                                <button class="btn ml-1" @click="bulkPublish(selections)">Publish</button>
                            </div>
                        </data-list-bulk-actions>
                        <data-list-filters
                            :per-page="perPage"
                            @per-page-changed="perPageChanged" />
                        <data-list-column-picker @change="updateColumns" />
                    </div>
                    <data-list-table :loading="loading" :allow-bulk-actions="true" @sorted="sorted">
                        <template slot="cell-title" slot-scope="{ row: entry }">
                            <a :href="entry.edit_url">{{ entry.title }}</a>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: entry }">
                            <span class="font-mono text-2xs">{{ entry.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: entry, index }">
                            <dropdown-list>
                                <ul class="dropdown-menu">
                                    <li><a :href="entry.permalink">View</a></li>
                                    <li><a :href="entry.edit_url">Edit</a></li>
                                    <li class="warning" v-if="entry.deleteable"><a @click.prevent="destroy(entry.id, index)">Delete</a></li>
                                </ul>
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
            }
        }

    },

    created() {
        this.request();
    },

    watch: {

        parameters() {
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

        destroy(id, index) {
            const url = cp_url(`collections/${this.collection}/entries/${id}`);
            axios.delete(url).then(response => {
                this.entries.splice(index, 1);
                this.$notify.success(__('Entry deleted'));
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            })
        },

        updateColumns() {
            //
        },

        perPageChanged(perPage) {
            this.perPage = perPage;
            this.page = 1;
        }

    }

}
</script>
