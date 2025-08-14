<script setup>
import { computed } from 'vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { Checkbox } from '@statamic/cms/ui';

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
        return __('select_all_items');
    }
    return anyItemsChecked.value ? __('deselect_all_items') : __('select_all_items');
}

function getScreenReaderText() {
    const totalItems = items.value.length;
    const selectedItems = selections.value.length;

    if (indeterminate.value) {
        return __('items_selected_count', { selected: selectedItems, total: totalItems });
    }

    if (anyItemsChecked.value) {
        return __('all_items_selected', { total: totalItems });
    }

    return __('no_items_selected', { total: totalItems });
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
