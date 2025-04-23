<template>
    <data-list :columns="columns" :rows="rows" v-slot="{ filteredRows: rows }">
        <CardPanel>
            <data-list-table>
                <template #cell-title="{ row: structure }">
                    <a
                        :href="structure.available_in_selected_site ? structure.show_url : structure.edit_url"
                        class="flex items-center"
                        v-text="__(structure.title)"
                    />
                </template>
                <template #actions="{ row: structure, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="structure.edit_url" />
                        <dropdown-item
                            v-if="structure.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${structure.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${structure.id}`"
                                :resource="structure"
                                @deleted="removeRow(structure)"
                            >
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </CardPanel>
    </data-list>
</template>

<script>
import Listing from '../Listing.vue';
import { CardPanel } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        CardPanel,
    },

    props: ['initialRows'],

    data() {
        return {
            rows: this.initialRows,
            columns: [{ label: __('Title'), field: 'title', visible: true }],
        };
    },
};
</script>
