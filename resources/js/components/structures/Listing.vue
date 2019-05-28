<template>
    <data-list :columns="columns" :rows="initialRows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: structure }">
                    <a :href="structure.show_url">{{ structure.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: structure, index }">
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li><a :href="structure.edit_url">Edit</a></li>
                            <li class="warning" v-if="structure.deleteable"><a @click.prevent="destroy(structure.id, index)">Delete</a></li>
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
        'initialRows',
    ],

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title', visible: true },
            ]
        }
    },

    methods: {
        destroy(id) {
            this.$axios.delete(`/cp/structures/${id}`);
        }
    }

}
</script>
