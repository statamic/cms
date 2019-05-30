<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="fieldsets">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table>
                <template slot="cell-title" slot-scope="{ row: fieldset }">
                    <a :href="fieldset.edit_url">{{ fieldset.title }}</a>
                </template>
                <template slot="cell-handle" slot-scope="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: fieldset }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="fieldset.edit_url" />
                        <dropdown-item :text="__('Delete')" class="warning" @click="destroy(fieldset.id)" />
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
export default {

    props: ['fieldsets'],

    data() {
        return {
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Fields'), field: 'fields' },
            ]
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
