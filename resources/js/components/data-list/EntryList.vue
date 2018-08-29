<template>
    <data-list :columns="columns" :rows="rows" :visible-columns="visibleColumns" :search-query="searchQuery">
        <div class="card p-0" slot-scope="{ }">
            <div class="data-list-header">
                <data-list-toggle-all></data-list-toggle-all>
                <data-list-search v-model="searchQuery"></data-list-search>
                <data-list-bulk-actions>
                    <div slot-scope="{ ids }" class="flex items-center" v-if="ids">
                        <button class="btn ml-1" @click="bulkDelete(ids)">Delete</button>
                        <button class="btn ml-1" @click="bulkUnpublish(ids)">Unpublish</button>
                        <button class="btn ml-1" @click="bulkPublish(ids)">Publish</button>
                    </div>
                </data-list-bulk-actions>
                <data-list-column-picker @change="updateColumns"></data-list-column-picker>
            </div>
            <data-table :allow-bulk-actions="true" @sorted="sorted">
                <template slot="actions" slot-scope="{ row: entry }">
                    <dropdown-list>
                        <li><a :href="entry.permalink">View</a></li>
                        <li><a :href="entry.edit_url">Edit</a></li>
                        <li class="warning"><a :href="entry.edit_url">Delete</a></li>
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
