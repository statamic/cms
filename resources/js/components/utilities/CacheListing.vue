<script setup>
import { computed, ref, useAttrs, useSlots } from 'vue';
import {
    Listing, ListingCustomizeColumns, ListingPagination, ListingSearch, ListingTable,
    Panel, PanelFooter, PanelHeader, Skeleton,
} from '@ui';
import CacheBulkActions from './CacheBulkActions.vue';

defineOptions({ inheritAttrs: false });

defineProps({
    allowSelections: {
        type: Boolean,
        default: true,
    },
    allowCustomizingColumns: {
        type: Boolean,
        default: true,
    },
    bulkAction: {
        type: String,
        default: 'Clear',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['bulk-action']);
const selections = defineModel('selections', {
    type: Array,
    default: () => [],
});
const attrs = useAttrs();
const slots = useSlots();
const listing = ref(null);
const cellSlots = computed(() => Object.keys(slots).filter((name) => name.startsWith('cell-')));

defineExpose({
    refresh: () => listing.value.refresh(),
});
</script>

<template>
    <Listing
        ref="listing"
        v-bind="attrs"
        :selections="allowSelections ? selections : undefined"
        :allow-bulk-actions="allowSelections"
        :allow-customizing-columns="allowCustomizingColumns"
        :allow-select-all-matching="false"
        :max-selections="1000"
        @update:selections="selections = $event"
        @update:search-query="selections = []"
    >
        <template #initializing>
            <div class="flex min-h-16 items-center gap-3">
                <Skeleton class="h-9 w-64" />
            </div>
            <Panel>
                <PanelHeader v-if="slots.breadcrumbs || slots['panel-actions']" class="flex items-center justify-between gap-2 px-1!">
                    <div class="min-h-8 min-w-0 flex-1">
                        <slot name="breadcrumbs" />
                    </div>
                    <div v-if="slots['panel-actions']" class="shrink-0 pe-2">
                        <slot name="panel-actions" />
                    </div>
                </PanelHeader>
                <Skeleton class="h-48 w-full" />
            </Panel>
        </template>

        <template #default>
            <div class="relative flex min-h-16 items-center gap-2 overflow-clip sm:gap-3 st-overflow-clip-margin">
                <div class="flex min-w-0 flex-1 items-center gap-2 overflow-x-auto py-1 ps-1 -ms-1 sm:gap-3">
                    <ListingSearch />
                </div>
                <ListingCustomizeColumns v-if="allowCustomizingColumns" />
            </div>

            <Panel class="relative overflow-x-auto overscroll-x-contain">
                <PanelHeader v-if="slots.breadcrumbs || slots['panel-actions']" class="flex items-center justify-between gap-2 px-1!">
                    <div class="min-h-8 min-w-0 flex-1">
                        <slot name="breadcrumbs" />
                    </div>
                    <div v-if="slots['panel-actions']" class="shrink-0 pe-2">
                        <slot name="panel-actions" />
                    </div>
                </PanelHeader>
                <ListingTable>
                    <template v-for="name in cellSlots" :key="name" #[name]="slotProps">
                        <slot :name="name" v-bind="slotProps" />
                    </template>
                </ListingTable>
                <PanelFooter>
                    <ListingPagination />
                </PanelFooter>
            </Panel>

            <CacheBulkActions
                v-if="allowSelections"
                :label="__(bulkAction)"
                :processing="processing"
                @run="emit('bulk-action', $event)"
            />
        </template>

        <template v-for="name in cellSlots" :key="name" #[name]="slotProps">
            <slot :name="name" v-bind="slotProps" />
        </template>
    </Listing>
</template>
