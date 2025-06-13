<template>
    <data-list :rows="rows" :columns="columns" v-slot="{}">
        <ui-panel>
            <data-list-table>
                <template #cell-title="{ row: role, index }">
                    <a :href="role.edit_url">{{ __(role.title) }}</a>
                </template>
                <template #cell-handle="{ value: handle }">
                    <span class="font-mono text-xs">{{ handle }}</span>
                </template>
                <template #actions="{ row: role, index }">
                    <Dropdown placement="left-start" class="me-3">
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="edit" :href="role.edit_url" />
                            <DropdownItem :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${role.id}`].confirm()" />
                        </DropdownMenu>
                    </Dropdown>

                    <resource-deleter
                        :ref="`deleter_${role.id}`"
                        :resource="role"
                        :requires-elevated-session="true"
                        @deleted="removeRow(role)"
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

    props: ['initialRows', 'initialColumns'],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns,
        };
    },
};
</script>
