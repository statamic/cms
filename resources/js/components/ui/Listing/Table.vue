<script setup>
import { Panel, PanelFooter } from '@statamic/ui';
import { ref, computed, useTemplateRef, useSlots } from 'vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import Pagination from './Pagination.vue';
import TableHead from './TableHead.vue';
import TableBody from './TableBody.vue';

const props = defineProps({
    unstyled: {
        type: Boolean,
        default: false,
    },
    contained: {
        type: Boolean,
        default: false,
    },
});

const { visibleColumns, selections, items, hasActions, showBulkActions, loading, reorderable } = injectListingContext();
const shifting = ref(false);
const hasSelections = computed(() => selections.value.length > 0);

const relativeColumnsSize = computed(() => {
    if (visibleColumns.value.length <= 4) return 'sm';
    if (visibleColumns.value.length <= 8) return 'md';
    if (visibleColumns.value.length >= 12) return 'lx';
    return 'xl';
});

const slots = useSlots();

const forwardedTableCellSlots = computed(() => {
    return Object.keys(slots)
        .filter((slotName) => slotName.startsWith('cell-'))
        .reduce((acc, slotName) => {
            acc[slotName] = slots[slotName];
            return acc;
        }, {});
});
</script>

<template>
    <table
        v-if="items.length > 0"
        :data-size="relativeColumnsSize"
        :class="{
            'select-none': shifting,
            'data-table': !unstyled,
            contained: contained,
            'opacity-50': loading,
        }"
        data-table
        ref="table"
        :data-has-selections="hasSelections ? true : null"
        @keydown.shift="shifting = true"
        @keyup="shifting = false"
    >
        <TableHead />
        <TableBody>
            <template v-if="$slots['tbody-start']" #tbody-start><slot name="tbody-start" /></template>
            <template v-if="$slots['prepended-row-actions']" #prepended-row-actions="slotProps">
                <slot name="prepended-row-actions" v-bind="slotProps" />
            </template>
            <template v-for="(slot, slotName) in forwardedTableCellSlots" :key="slotName" #[slotName]="slotProps">
                <component :is="slot" v-bind="slotProps" />
            </template>
        </TableBody>
    </table>
    <div v-if="items.length === 0">
        <div class="text-center text-gray-500 text-sm pt-4">
            {{ __('No items found') }}
        </div>
    </div>
</template>
