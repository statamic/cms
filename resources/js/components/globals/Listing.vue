<template>
    <data-list :rows="rows" :columns="columns" v-slot="{ filteredRows: rows }">
        <CardPanel>
            <data-list-table>
                <template #cell-title="{ row: global }">
                    <a :href="global.edit_url">{{ __(global.title) }}</a>
                </template>
                <template #cell-handle="{ value: handle }">
                    <span class="text-2xs font-mono">{{ handle }}</span>
                </template>
                <template #actions="{ row: global, index }">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="global.edit_url" />
                        <dropdown-item
                            v-if="global.deleteable"
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${global.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${global.id}`"
                                :resource="global"
                                @deleted="removeRow(global)"
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

    props: ['globals'],

    data() {
        return {
            rows: this.globals,
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Handle'), field: 'handle' },
            ],
        };
    },
};
</script>
