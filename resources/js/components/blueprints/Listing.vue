<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows" :sort="false" v-slot="{ filteredRows: rows }">
        <ui-panel>
            <data-list-table :reorderable="reorderable" @reordered="$emit('reordered', $event)">
                <template #cell-title="{ row: blueprint }">
                    <div class="flex items-center">
                        <div
                            class="little-dot ltr:mr-2 rtl:ml-2"
                            :class="[blueprint.hidden ? 'hollow' : 'bg-green-600']"
                        />
                        <a :href="blueprint.edit_url">{{ __(blueprint.title) }}</a>
                    </div>
                </template>
                <template #cell-handle="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template #actions="{ row: blueprint, index }">
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
                                @deleted="removeRow(blueprint)"
                            >
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </ui-panel>
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
            ],
        };
    },

    watch: {
        initialRows(rows) {
            this.rows = rows;
        },
    },
};
</script>
