<script setup>
import { computed, useSlots } from 'vue';
import { Listing, ListingTable, Panel, Subheading } from '@ui';
import { groupResourceIndexItems } from './group-items.js';

const emit = defineEmits(['refreshing']);

const props = defineProps({
    resourceIndex: {
        type: Object,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    actionUrl: {
        type: String,
        default: null,
    },
});
const slots = useSlots();
const grouped = computed(() => props.resourceIndex.hasSavedGroups || props.resourceIndex.groups.length > 0);
const listingColumns = computed(() => props.resourceIndex.hasSavedGroups
    ? props.columns.map((column) => ({ ...column, sortable: false }))
    : props.columns,
);
const forwardedTableCellSlots = computed(() => Object.keys(slots)
    .filter((slotName) => slotName.startsWith('cell-'))
    .reduce((forwardedSlots, slotName) => {
        forwardedSlots[slotName] = slots[slotName];
        return forwardedSlots;
    }, {}),
);

function groupsFor(items) {
    return groupResourceIndexItems(items, props.resourceIndex);
}
</script>

<template>
    <Listing
        :items="items"
        :columns="listingColumns"
        :action-url="actionUrl"
        :allow-search="false"
        :allow-customizing-columns="false"
        :sortable="!resourceIndex.hasSavedGroups"
        @refreshing="emit('refreshing')"
    >
        <template #default="{ items }">
            <div
                v-if="!items.length"
                class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center text-gray-500"
                v-text="__('No results')"
            />

            <div v-else class="space-y-6">
                <section v-for="group in groupsFor(items)" :key="group.id">
                    <Subheading v-if="group.title" size="lg" class="mb-2" :text="group.title" />

                    <Panel class="relative overflow-x-auto" style="container-type: scroll-state;">
                        <ListingTable :items="grouped ? group.items : undefined">
                            <template
                                v-for="(slot, slotName) in forwardedTableCellSlots"
                                :key="slotName"
                                #[slotName]="slotProps"
                            >
                                <component :is="slot" v-bind="slotProps" />
                            </template>
                            <template v-if="$slots['prepended-row-actions']" #prepended-row-actions="{ row }">
                                <slot name="prepended-row-actions" :row="row" />
                            </template>
                        </ListingTable>
                    </Panel>
                </section>
            </div>
        </template>
    </Listing>
</template>
