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
                    <Dropdown placement="left-start" class="me-3">
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="edit" :href="blueprint.edit_url" />
                            <DropdownItem :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${blueprint.id}`].confirm()" />
                        </DropdownMenu>
                    </Dropdown>

                    <resource-deleter
                        :ref="`deleter_${blueprint.id}`"
                        :resource="blueprint"
                        @deleted="removeRow(blueprint)"
                    />
                </template>
            </data-list-table>
        </ui-panel>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import { Dropdown, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: { Dropdown, DropdownMenu, DropdownLabel, DropdownSeparator, DropdownItem },

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
