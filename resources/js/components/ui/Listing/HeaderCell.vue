<script setup>
import { Button } from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import { computed } from 'vue';

const props = defineProps({
    column: {
        type: Object,
        required: true,
    },
});

const { sortColumn, setSortColumn } = injectListingContext();
const isCurrentSortColumn = computed(() => props.column.field === sortColumn.value);
</script>

<template>
    <th scope="col">
        <span v-if="!column.sortable" v-text="__(column.label)" />
        <Button
            v-else
            :text="__(column.label)"
            :icon-append="isCurrentSortColumn ? 'up-down' : null"
            :aria-label="`Sort by ${__(column.label)}${isCurrentSortColumn ? ' (currently sorted)' : ''}`"
            size="sm"
            variant="ghost"
            class="-mt-2 -mb-1 -ml-3 text-sm! font-medium! text-gray-900! dark:text-gray-400!"
            @click.prevent="setSortColumn(column.field)"
        />
    </th>
</template>
