<template>
    <data-list :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <Panel>
            <data-list-table :rows="rows">
                <template #cell-title="{ row: taxonomy }">
                    <a :href="taxonomy.terms_url">{{ __(taxonomy.title) }}</a>
                </template>
                <template #actions="{ row: taxonomy, index }">
                    <dropdown-list placement="left-start">
                        <dropdown-item :text="__('Edit')" :redirect="taxonomy.edit_url" />
                        <dropdown-item :text="__('Edit Blueprints')" :redirect="taxonomy.blueprints_url" />
                        <dropdown-item
                            v-if="taxonomy.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${taxonomy.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${taxonomy.id}`"
                                :resource="taxonomy"
                                @deleted="removeRow(taxonomy)"
                            >
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </Panel>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import { Panel } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Panel,
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
