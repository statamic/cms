<template>
    <data-list :columns="columns" :rows="rows" :visible-columns="visibleColumns" :search-query="searchQuery" :pagination="true">
        <div class="card p-0" slot-scope="{ }">
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
    </data-list>
</template>

<script>
export default {
    props: [
        'columns',
        'initial-rows',
        'visible-columns'
    ],
    data() {
        return {
            rows: this.initialRows,
            searchQuery: ''
        }
    },
    methods: {
        updateColumns(columns) {
            //TODO: Add axios call and store the updated visible columns
        },
        search() {
            //TODO: Axios call & update rows
        },
        bulkDelete(ids) {
            //TODO: Axios call & update rows
        },
        bulkUnpublish(ids) {
            //TODO: Axios call & update rows
        },
        bulkPublish(ids) {
            //TODO: Axios call & update rows
        },
        sorted(column, direction) {
            //TODO: Axios call & update rows
        }
    }
}
</script>
