<script setup>
import { computed } from 'vue';
import { injectListingContext } from '../Listing/Listing.vue';
import { Checkbox } from '@ui';
import {
    isPageFullySelected,
    isPagePartiallySelected,
    pageItemIds,
    removePageSelections,
    unionPageSelections,
} from '@/util/listing-selections.js';

const {
    items,
    selections,
    maxSelections,
    clearSelections,
    reorderable,
    allMatchingSelected,
    meta,
} = injectListingContext();

const pageFullySelected = computed(() => isPageFullySelected(items.value, selections.value));
const indeterminate = computed(() => isPagePartiallySelected(items.value, selections.value));
const pageSize = computed(() => items.value.length);
const selectedOnPageCount = computed(() =>
    pageItemIds(items.value).filter((id) => selections.value.includes(id)).length,
);

function toggle(checked) {
    if (checked) {
        selectPageItems();
        return;
    }

    if (allMatchingSelected.value) {
        clearSelections();
        return;
    }

    deselectPageItems();
}

function selectPageItems() {
    const next = unionPageSelections(
        selections.value,
        pageItemIds(items.value),
        maxSelections.value ?? Infinity,
    );
    selections.value.splice(0, selections.value.length, ...next);
}

function deselectPageItems() {
    const next = removePageSelections(selections.value, pageItemIds(items.value));
    selections.value.splice(0, selections.value.length, ...next);
}

function getAriaLabel() {
    if (indeterminate.value) {
        return __('Select items');
    }

    return pageFullySelected.value ? __('Deselect items') : __('Select items');
}

function getScreenReaderText() {
    const totalItems = allMatchingSelected.value ? (meta.value?.total ?? pageSize.value) : pageSize.value;
    const selectedItems = selectedOnPageCount.value;

    if (indeterminate.value) {
        return __('messages.selections_select_all', { selected: selectedItems, total: totalItems });
    }

    if (pageFullySelected.value) {
        return __('messages.selections_click_to_deselect_all', { total: totalItems });
    }

    return __('messages.selections_click_to_select_all', { total: totalItems });
}
</script>

<template>
    <Checkbox
        v-if="!reorderable"
        :model-value="pageFullySelected"
        :indeterminate="indeterminate"
        :label="getAriaLabel()"
        :description="getScreenReaderText()"
        :value="'all'"
        size="sm"
        solo
        @update:model-value="toggle"
    />
</template>
