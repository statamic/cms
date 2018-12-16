<template>
    <div>

        <div v-if="loading" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!loading"
            :rows="entries"
            :columns="columns"
            :search-query="searchQuery"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ filteredRows: rows }">
                <div class="card p-0">
                    <div class="data-list-header">
                        <data-list-toggle-all></data-list-toggle-all>
                        <data-list-search v-model="searchQuery"></data-list-search>
                        <data-list-bulk-actions>
                            <div slot-scope="{ selections, hasSelections }" class="flex items-center" v-if="hasSelections">
                                <button class="btn ml-1" @click="bulkDelete(selections)">Delete</button>
                                <button class="btn ml-1" @click="bulkUnpublish(selections)">Unpublish</button>
                                <button class="btn ml-1" @click="bulkPublish(selections)">Publish</button>
                            </div>
                        </data-list-bulk-actions>
                        <data-list-column-picker @change="updateColumns"></data-list-column-picker>
                    </div>
                    <data-table :allow-bulk-actions="true" @sorted="sorted">
                        <template slot="cell-title" slot-scope="{ row: entry }">
                            <a :href="entry.edit_url">{{ entry.title }}</a>
                        </template>
                        <template slot="cell-slug" slot-scope="{ row: entry }">
                            <span class="font-mono text-2xs">{{ entry.slug }}</span>
                        </template>
                        <template slot="actions" slot-scope="{ row: entry }">
                            <dropdown-list>
                                <ul class="dropdown-menu">
                                    <li><a :href="entry.permalink">View</a></li>
                                    <li><a :href="entry.edit_url">Edit</a></li>
                                    <li class="warning"><a :href="entry.edit_url">Delete</a></li>
                                </ul>
                            </dropdown-list>
                        </template>
                    </data-table>
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
        collection: String
    },

    data() {
        return {
            loading: true,
            entries: [],
            columns: [],
            sortColumn: null,
            sortDirection: 'asc',
            meta: null,
            page: 1,
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
            });
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        destroy(id, index) {
            //
        },

        updateColumns() {
            //
        }

    }

}
</script>
