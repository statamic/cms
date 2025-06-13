<template>
    <data-list :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <Panel>
            <data-list-table :rows="rows">
                <template #cell-title="{ row: taxonomy }">
                    <a :href="taxonomy.terms_url">{{ __(taxonomy.title) }}</a>
                </template>
                <template #actions="{ row: taxonomy, index }">
                    <Dropdown>
                        <DropdownMenu>
                            <DropdownItem :text="__('Edit')" icon="cog" :href="taxonomy.edit_url" />
                            <DropdownItem :text="__('Edit Blueprints')" icon="blueprint-edit" :href="taxonomy.blueprints_url" />
                            <DropdownItem :text="__('Delete Taxonomy')" icon="trash" variant="destructive" @click="$refs[`deleter_${taxonomy.id}`].confirm()" />
                        </DropdownMenu>
                    </Dropdown>

                    <resource-deleter
                        :ref="`deleter_${taxonomy.id}`"
                        :resource="taxonomy"
                        @deleted="removeRow(taxonomy)"
                    />
                </template>
            </data-list-table>
        </Panel>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import { Panel, Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Panel,
        Dropdown,
        DropdownMenu,
        DropdownItem,
    },

    props: ['initial-rows', 'initial-columns'],

    data() {
        return {
            rows: this.initialRows,
            columns: this.initialColumns,
        };
    },
};
</script>
