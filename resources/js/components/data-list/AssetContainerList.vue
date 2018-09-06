<template>
    <data-list :visible-columns="visibleColumns" :columns="columns" :rows="initialRows">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: container }">
                    <a :href="container.edit_url">{{ container.title }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: container, index }">
                    <dropdown-list>
                        <li><a :href="container.edit_url">Edit</a></li>
                        <li class="warning"><a @click.prevent="destroy(container, index)">Delete</a></li>
                    </dropdown-list>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
import axios from 'axios';

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
            if (confirm('Are you sure?')) {
                axios.delete(container.delete_url).then(response => {
                    this.rows.splice(index, 1);
                });
            }
        }

    }

}
</script>
