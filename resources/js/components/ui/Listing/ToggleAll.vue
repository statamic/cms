<script setup>
import { computed } from 'vue';
import { injectListingContext } from '../Listing/Listing.vue';
import { Checkbox } from '@ui';

const props = defineProps({
    items: {
        type: Array,
    },
});

const { items: listingItems, selections, maxSelections, clearSelections, reorderable } = injectListingContext();
const items = computed(() => props.items ?? listingItems.value);
const hasItemScope = computed(() => props.items !== undefined);
const itemIds = computed(() => items.value.map((item) => item.id));
const selectedItemIds = computed(() => itemIds.value.filter((id) => selections.value.includes(id)));
const anyItemsChecked = computed(() => {
    if (hasItemScope.value) {
        return selectedItemIds.value.length > 0;
    }

    return selections.value.length > 0;
});
const allItemsChecked = computed(() => selectedItemIds.value.length === items.value.length);
const indeterminate = computed(() => {
    if (!anyItemsChecked.value) {
        return false;
    }

    if (hasItemScope.value) {
        return !allItemsChecked.value;
    }

    return selections.value.length < items.value.length;
});
const checkboxValue = computed(() => {
    if (hasItemScope.value) {
        return allItemsChecked.value;
    }

    return anyItemsChecked.value;
});

function toggle() {
    if (hasItemScope.value) {
        if (anyItemsChecked.value) {
            clearItemSelections();
        } else {
            selectItemsUpToLimit();
        }

        return;
    }

    if (anyItemsChecked.value) {
        clearSelections();
    } else {
        selectItemsUpToLimit();
    }
}

function selectItemsUpToLimit() {
    let newSelections = itemIds.value;

    if (hasItemScope.value) {
        const unselectedItemIds = itemIds.value.filter((id) => !selections.value.includes(id));
        newSelections = [...selections.value, ...unselectedItemIds];
    }

    if (maxSelections.value) {
        newSelections = newSelections.slice(0, maxSelections.value);
    }

    selections.value.splice(0, selections.value.length, ...newSelections);
}

function clearItemSelections() {
    const newSelections = selections.value.filter((id) => !itemIds.value.includes(id));
    selections.value.splice(0, selections.value.length, ...newSelections);
}

function getAriaLabel() {
    if (indeterminate.value) {
        return __('Select all items');
    }

    if (anyItemsChecked.value) {
        return __('Deselect all items');
    }

    return __('Select all items');
}

function getScreenReaderText() {
    const totalItems = items.value.length;
    const selectedItems = selectedItemIds.value.length;

    if (indeterminate.value) {
        return __('messages.selections_select_all', { selected: selectedItems, total: totalItems });
    }

    if (allItemsChecked.value) {
        return __('messages.selections_click_to_deselect_all', { total: totalItems });
    }

    return __('messages.selections_click_to_select_all', { total: totalItems });
}
</script>

<template>
    <Checkbox
        v-if="!reorderable"
        :model-value="checkboxValue"
        :indeterminate="indeterminate"
        :label="getAriaLabel()"
        :description="getScreenReaderText()"
        :value="'all'"
        size="sm"
        solo
        @update:model-value="toggle"
    />
</template>
