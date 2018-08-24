<template>
    <data-list :visible-columns="visibleColumns" :columns="columns" :rows="filteredRows">
        <div class="card p-0" slot-scope="{}">
            <div class="data-list-header">
                <data-list-toggle-all></data-list-toggle-all>
                <data-list-search v-model="searchQuery"></data-list-search>
                <data-list-bulk-actions>
                    <div slot-scope="{ ids, hasCheckedIds }" class="flex items-center" v-if="hasCheckedIds">
                        <button class="btn ml-1" @click="bulkDelete(ids)">Delete</button>
                        <button class="btn ml-1" @click="bulkUnpublish(ids)">Unpublish</button>
                        <button class="btn ml-1" @click="bulkPublish(ids)">Publish</button>
                    </div>
                </data-list-bulk-actions>
                <data-list-column-picker @change="updateColumns"></data-list-column-picker>
            </div>
            <data-table :allow-bulk-actions="true">
                <template slot="actions" slot-scope="{ row: entry }">
                    <a class="text-xs text-blue" :href="entry.permalink">View</a>
                    <a class="text-xs text-blue ml-1" :href="entry.edit_url">Edit</a>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
export default {
    props: [
        'initial-rows',
        'columns',
        'visible-columns'
    ],
    data() {
        return {
            rows: this.initialRows,
            searchQuery: ''
        }
    },
    computed: {
        filteredRows() {
            return this.rows.filter(row => {
                return row.title.toLowerCase().includes(this.searchQuery.toLowerCase());
            });
        }
    },
    methods: {
        updateColumns(columns) {
            //TODO: Add axios call and store the updated visible columns
        },
        search(query) {
            this.searchQuery = query;
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
        }
    }
}
</script>
