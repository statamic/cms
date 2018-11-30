<template>
    <data-list :rows="rows" :columns="columns">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table>
                <template slot="cell-title" slot-scope="{ row: global }">
                    <a :href="global.edit_url">{{ global.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value: handle }">
                    <span class="font-mono text-2xs">{{ handle }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: global, index }">
                    <dropdown-list>
                        <li><a :href="global.edit_url">Edit</a></li>
                        <li class="warning" v-if="global.deleteable"><a @click.prevent="destroy(global.id, index)">Delete</a></li>
                    </dropdown-list>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
import axios from 'axios';

export default {

    props: ['globals'],

    data() {
        return {
            rows: this.globals,
            columns: ['title', 'handle']
        }
    },

    methods: {

        destroy(id, index) {
            const url = cp_url(`globals/${id}`);
            axios.delete(url).then(response => {
                this.rows.splice(index, 1);
                this.$notify.success(__('Global set deleted'));
            }).catch(error => {
                this.$notify.error(error.response.data.message);
            })
        }

    }
}
</script>
