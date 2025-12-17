<script setup>
import { Button } from '@ui';
import { injectListingContext } from '../Listing/Listing.vue';
import { computed } from 'vue';

const props = defineProps({
    column: {
        type: Object,
        required: true,
    },
});

const { sortColumn, sortDirection, setSortColumn } = injectListingContext();
const isCurrentSortColumn = computed(() => props.column.field === sortColumn.value);
const sortIcon = computed(() => {
    if (!isCurrentSortColumn.value) return null;
    return sortDirection.value === 'asc' ? 'sort-asc' : 'sort-desc';
});
</script>

<template>
    <th scope="col">
        <span v-if="!column.sortable" v-text="__(column.label)" />
        <Button
            v-else
            :text="__(column.label)"
            :icon-append="sortIcon"
            size="sm"
            variant="ghost"
            class="-mt-2 -mb-1 -ms-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
            @click.prevent="setSortColumn(column.field)"
        />
    </th>
</template>
