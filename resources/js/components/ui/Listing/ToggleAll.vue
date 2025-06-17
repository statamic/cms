<script setup>
import { computed } from 'vue';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';

const { items, selections, maxSelections, clearSelections } = injectListingContext();
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
