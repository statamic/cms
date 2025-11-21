<script setup>
import { computed } from 'vue';
import { injectListingContext } from './listingContext.js';
import { Checkbox } from '@ui';

const { items, selections, maxSelections, clearSelections, reorderable } = injectListingContext();
const anyItemsChecked = computed(() => selections.value.length > 0);
const indeterminate = computed(() => anyItemsChecked.value && selections.value.length < items.value.length);

function toggle() {
    anyItemsChecked.value ? clearSelections() : checkMaximumAmountOfItems();
}

function checkMaximumAmountOfItems() {
    let newSelections = items.value.map((row) => row.id);
    if (maxSelections.value) newSelections = newSelections.slice(0, maxSelections.value);
    selections.value.splice(0, selections.value.length, ...newSelections);
}

function getAriaLabel() {
    if (indeterminate.value) {
        return __('Select all items');
    }

    return anyItemsChecked.value ? __('Deselect all items') : __('Select all items');
}

function getScreenReaderText() {
    const totalItems = items.value.length;
    const selectedItems = selections.value.length;

    if (indeterminate.value) {
        return __('messages.selections_select_all', { selected: selectedItems, total: totalItems });
    }

    if (anyItemsChecked.value) {
        return __('messages.selections_click_to_deselect_all', { total: totalItems });
    }

    return __('messages.selections_click_to_select_all', { total: totalItems });
}
</script>

<template>
    <Checkbox
        v-if="!reorderable"
        :model-value="anyItemsChecked"
        :indeterminate="indeterminate"
        :label="getAriaLabel()"
        :description="getScreenReaderText()"
        :value="'all'"
        size="sm"
        solo
        @update:model-value="toggle"
    />
</template>
