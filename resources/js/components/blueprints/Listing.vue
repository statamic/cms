<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="blueprints">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-table>
                <template slot="cell-title" slot-scope="{ row: blueprint }">
                    <a :href="blueprint.edit_url">{{ blueprint.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: blueprint }">
                    <dropdown-list>
                        <ul class="dropdown-menu">
                            <li><a :href="blueprint.edit_url">Edit</a></li>
                            <li class="warning"><a @click.prevent="destroy(blueprint.id)">Delete</a></li>
                        </ul>
                    </dropdown-list>
                </template>
            </data-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['blueprints'],

    data() {
        return {
            columns: ['title', 'handle', 'sections', 'fields']
        }
    },

    methods: {

        destroy(handle) {
            if (confirm('Are you sure?')) {
                console.log(`Deleting blueprint ${handle}`); // TODO
            }
        }

    }
}
</script>
