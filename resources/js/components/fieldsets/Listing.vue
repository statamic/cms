<template>
    <data-list :visible-columns="columns" :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <ui-panel>
            <data-list-table>
                <template #cell-title="{ row: fieldset }">
                    <a :href="fieldset.edit_url">{{ __(fieldset.title) }}</a>
                </template>
                <template #cell-handle="{ value }">
                    <span class="font-mono text-xs">{{ value }}</span>
                </template>
                <template #actions="{ row: fieldset, index }">
                    <Dropdown>
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="edit" :href="fieldset.edit_url" />
                            <DropdownItem v-if="fieldset.is_resettable" :text="__('Reset')" variant="destructive" @click="$refs[`resetter_${fieldset.id}`].confirm()" />
                            <DropdownItem v-if="fieldset.is_deletable" :text="__('Delete')" icon="trash" variant="destructive" @click="$refs[`deleter_${fieldset.id}`].confirm()" />
                        </DropdownMenu>
                    </Dropdown>

                    <fieldset-resetter :ref="`resetter_${fieldset.id}`" :resource="fieldset" :reload="true" />
                    <fieldset-deleter :ref="`deleter_${fieldset.id}`" :resource="fieldset" @deleted="removeRow(fieldset)" />
                </template>
            </data-list-table>
        </ui-panel>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import { Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui';
import FieldsetDeleter from './FieldsetDeleter.vue';
import FieldsetResetter from './FieldsetResetter.vue';

export default {
    mixins: [Listing],

    components: {
        Dropdown,
        DropdownMenu,
        DropdownItem,
        FieldsetDeleter,
        FieldsetResetter,
    },

    props: ['initialRows'],

    data() {
        return {
            rows: this.initialRows,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle', width: '25%' },
                { label: __('Fields'), field: 'fields', width: '15%' },
            ],
        };
    },
};
</script>
