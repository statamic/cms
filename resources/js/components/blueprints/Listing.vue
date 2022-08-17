<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows" :sort="false">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
            <data-list-table
                :reorderable="reorderable"
                @reordered="$emit('reordered', $event)"
            >
                <template slot="cell-title" slot-scope="{ row: blueprint }">
                    <div class="flex items-center">
                        <div class="little-dot mr-1" :class="[blueprint.hidden ? 'hollow' : 'bg-green']" />
                        <a :href="blueprint.edit_url">{{ blueprint.title }}</a>
                    </div>
                </template>
                <template slot="cell-handle" slot-scope="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template slot="actions" slot-scope="{ row: blueprint, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="blueprint.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${blueprint.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${blueprint.id}`"
                                :resource="blueprint"
                                @deleted="removeRow(blueprint)">
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </div>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';

export default {

    mixins: [Listing],

    props: ['initialRows', 'reorderable'],

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Fields'), field: 'fields' },
            ]
        }
    },

    watch: {

        initialRows(rows) {
            this.rows = rows;
        }

    }

}
</script>
