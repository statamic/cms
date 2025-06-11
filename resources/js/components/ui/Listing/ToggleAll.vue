<script setup>
import { computed } from 'vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';

const { items, selections, maxSelections } = injectListingContext();
const anyItemsChecked = computed(() => selections.value.length > 0);
const indeterminate = computed(() => anyItemsChecked.value && selections.value.length < items.value.length);

function toggle() {
    anyItemsChecked.value ? uncheckAllItems() : checkMaximumAmountOfItems();
}

function checkMaximumAmountOfItems() {
    let newSelections = items.value.map((row) => row.id);
    if (maxSelections) newSelections = newSelections.slice(0, maxSelections);
    selections.value.splice(0, selections.value.length, ...newSelections);
}

function uncheckAllItems() {
    selections.value.splice(0, selections.value.length);
}
</script>

<template>
    <label for="checkerOfAllBoxes" class="relative flex cursor-pointer items-center justify-center">
        <input
            type="checkbox"
            @change="toggle"
            :checked="anyItemsChecked"
            :indeterminate="indeterminate"
            id="checkerOfAllBoxes"
            class="relative top-0"
        />
    </label>
</template>
