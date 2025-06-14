<template>
    <ui-panel>
        <data-list :rows="rows" :columns="columns" v-slot="{}">
            <data-list-table>
                <template #cell-title="{ row: group, index }">
                    <a :href="group.show_url">{{ __(group.title) }}</a>
                </template>
                <template #cell-handle="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template #actions="{ row: group, index }">
                    <Dropdown placement="left-start" class="me-3">
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="edit" :href="group.edit_url" />
                            <DropdownItem :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${group.id}`].confirm()" />
                        </DropdownMenu>
                    </Dropdown>

                    <resource-deleter :ref="`deleter_${group.id}`" :resource="group" @deleted="removeRow(group)" />
                </template>
            </data-list-table>
        </data-list>
    </ui-panel>
</template>

<script>
import Listing from '../Listing.vue';
import { Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Dropdown,
        DropdownMenu,
        DropdownItem,
    },

    props: {
        initialRows: Array,
    },

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
                { label: __('Users'), field: 'users' },
                { label: __('Roles'), field: 'roles' },
            ],
        };
    },
};
</script>
