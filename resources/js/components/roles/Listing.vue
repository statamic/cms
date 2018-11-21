<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table :rows="rows">
                <template slot="cell-title" slot-scope="{ row: role }">
                    <a :href="role.edit_url">{{ role.title }}</a>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
import axios from 'axios';

export default {

    props: {
        initialRows: Array,
    },

    data() {
        return {
            rows: this.initialRows,
            columns: ['title']
        }
    }

}
</script>
