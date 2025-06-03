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
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" :redirect="group.edit_url" />
                        <dropdown-item
                            :text="__('Delete')"
                            class="warning"
                            @click="$refs[`deleter_${group.id}`].confirm()"
                        >
                            <resource-deleter
                                :ref="`deleter_${group.id}`"
                                :resource="group"
                                @deleted="removeRow(group)"
                            >
                            </resource-deleter>
                        </dropdown-item>
                    </dropdown-list>
                </template>
            </data-list-table>
        </data-list>
    </ui-panel>
</template>

<script>
import Listing from '../Listing.vue';

export default {
    mixins: [Listing],

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
