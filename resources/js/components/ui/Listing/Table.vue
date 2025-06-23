<script setup>
import { Panel, PanelFooter } from '@statamic/ui';
import { ref, computed, useTemplateRef, useSlots } from 'vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import ToggleAll from './ToggleAll.vue';
import Pagination from './Pagination.vue';
import HeaderCell from './HeaderCell.vue';
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

const { visibleColumns, selections, hasActions, showBulkActions, maxSelections, loading, reorderable } =
    injectListingContext();
const shifting = ref(false);
const hasSelections = computed(() => selections.value.length > 0);
const singleSelect = computed(() => maxSelections === 1);

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
    <Panel class="relative overflow-x-auto overscroll-x-contain">
        <table
            :data-size="relativeColumnsSize"
            :class="{
                'select-none': shifting,
                'data-table': !unstyled,
                contained: contained,
                'opacity-50': loading,
            }"
            data-table
            ref="table"
            tabindex="0"
            :data-has-selections="hasSelections ? true : null"
            @keydown.shift="shifting = true"
            @keyup="shifting = false"
        >
            <thead v-if="showBulkActions || visibleColumns.length > 1">
                <tr>
                    <th
                        v-if="showBulkActions || reorderable"
                        :class="{ 'checkbox-column': !reorderable, 'handle-column': reorderable }"
                    >
                        <ToggleAll v-if="showBulkActions && !singleSelect" />
                    </th>
                    <HeaderCell v-for="column in visibleColumns" :key="column.field" :column />
                    <!--                    <th class="type-column" v-if="type">-->
                    <!--                        <template v-if="type === 'entries'">{{ __('Collection') }}</template>-->
                    <!--                        <template v-if="type === 'terms'">{{ __('Taxonomy') }}</template>-->
                    <!--                    </th>-->
                    <th class="actions-column" v-if="hasActions" />
                </tr>
            </thead>
            <TableBody>
                <template v-for="(slot, slotName) in forwardedTableCellSlots" :key="slotName" #[slotName]="slotProps">
                    <component :is="slot" v-bind="slotProps" />
                </template>
            </TableBody>
        </table>
        <PanelFooter>
            <Pagination />
        </PanelFooter>
    </Panel>
</template>
