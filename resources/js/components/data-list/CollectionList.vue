<template>
    <data-list :visible-columns="visibleColumns" :columns="columns" :rows="initialRows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: collection }">
                    <a :href="collection.entries_url">{{ collection.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: collection }">
                    <dropdown-list>
                        <li><a :href="collection.edit_url">Edit</a></li>
                        <li class="warning"><a @click.prevent="deleteCollection(collection.id)">Delete</a></li>
                    </dropdown-list>
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
