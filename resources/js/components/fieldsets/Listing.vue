<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="fieldsets">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table>
                <template slot="cell-title" slot-scope="{ row: fieldset }">
                    <a :href="fieldset.edit_url">{{ fieldset.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: fieldset }">
                    <dropdown-list>
                        <li><a :href="fieldset.edit_url">Edit</a></li>
                        <li class="warning"><a @click.prevent="destroy(fieldset.id)">Delete</a></li>
                    </dropdown-list>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['fieldsets'],

    data() {
        return {
            columns: ['title', 'handle', 'fields']
        }
    },

    methods: {

        destroy(handle) {
            if (confirm('Are you sure?')) {
                console.log(`Deleting fieldset ${handle}`); // TODO
            }
        }

    }
}
</script>
