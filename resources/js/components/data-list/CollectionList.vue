<template>
    <data-list :visible-columns="visibleColumns" :columns="columns" :rows="rows">
        <div class="card p-0" slot-scope="{}">
            <div class="data-list-header">
                <data-list-bulk-actions>
                    <div slot-scope="{ ids, hasCheckedIds }" class="flex items-center" v-if="hasCheckedIds">
                        <button class="btn ml-1" @click="bulkDelete(ids)">Delete</button>
                    </div>
                </data-list-bulk-actions>
                <data-list-column-picker @change="updateColumns"></data-list-column-picker>
            </div>
            <data-table :allow-bulk-actions="true">
                <template slot="actions" slot-scope="{ row: collection }">
                    <a class="text-xs text-blue" :href="collection.entries_url">View Entries</a>
                    <a class="text-xs text-blue ml-1" :href="collection.edit_url">Edit</a>
                    <a class="text-xs text-red ml-1" @click.prevent="deleteCollection(collection.id)">Delete</a>
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
            rows: this.initialRows
        }
    },
    methods: {
        updateColumns(columns) {
            //TODO: Add axios call and store the updated visible columns
        },
        bulkDelete(ids) {
            //TODO: Axios call & update rows
        },
        deleteCollection(id) {
            if (confirm('Are you sure?')) {
                // TODO: Axios call to delete it.
                console.log(`Deleting collection ${id}`);
            }
        }
    }
}
</script>
