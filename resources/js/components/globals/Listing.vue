<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="globals">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table>
                <template slot="cell-title" slot-scope="{ row: global }">
                    <a :href="global.edit_url">{{ global.slug }}</a>
                </template>
                <template slot="actions" slot-scope="{ row: global }">
                    <dropdown-list>
                        <li><a :href="global.edit_url">Edit</a></li>
                        <li class="warning"><a @click.prevent="destroy(global.id)">Delete</a></li>
                    </dropdown-list>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['globals'],

    data() {
        return {
            columns: ['title']
        }
    },

    methods: {

        destroy(handle) {
            if (confirm('Are you sure?')) {
                console.log(`Deleting global ${handle}`); // TODO
            }
        }

    }
}
</script>
