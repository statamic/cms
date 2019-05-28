<template>
    <data-list :columns="columns" :rows="initialRows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: collection }">
                    <a :href="collection.entries_url">{{ collection.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: collection }">
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li><a :href="collection.edit_url">Edit</a></li>
                            <li class="warning" v-if="collection.deleteable">
                                <a @click.prevent="destroy(collection.id)">Delete</a>
                            </li>
                        </ul>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
export default {
    props: [
        'initial-rows',
        'columns',
    ],
    data() {
        return {
            rows: this.initialRows
        }
    },
    methods: {
        destroy(id) {
            if (confirm('Are you sure?')) {
                this.$axios.delete(`/cp/collections/${id}`);
            }
        }
    }
}
</script>
