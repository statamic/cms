<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="blueprints">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: blueprint }">
                    <a :href="blueprint.edit_url">{{ blueprint.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: blueprint }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="blueprint.edit_url" />
                        <dropdown-item :text="__('Delete')" class="warning" @selected="destroy(blueprint.id)" />
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['blueprints'],

    data() {
        return {
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Sections'), field: 'sections' },
                { label: __('Fields'), field: 'fields' },
            ]
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
