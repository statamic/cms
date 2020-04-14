<template>
    <data-list :visible-columns="visibleColumns" :columns="columns" :rows="initialRows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: container }">
                    <a :href="container.edit_url">{{ container.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: container, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="container.edit_url" />
                        <dropdown-item :text="__('Delete')" class="warning" @click="destroy(container, index)" />
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
        'visible-columns'
    ],

    data() {
        return {
            rows: this.initialRows
        }
    },

    methods: {

        destroy(container, index) {
            if (confirm(__('Are you sure?'))) {
                this.$axios.delete(container.delete_url).then(response => {
                    this.rows.splice(index, 1);
                });
            }
        }

    }

}
</script>
