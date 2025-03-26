<script setup>
import { DropdownMenuItem } from 'reka-ui';
import { useSlots } from 'vue';

defineProps({
    href: { type: String, default: null },
    icon: { type: String, default: null },
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;

</script>

<template>
    <DropdownMenuItem class="
        col-span-2 grid grid-cols-subgrid items-center
        py-1.5 px-1 rounded-lg text-sm antialiased
        not-data-disabled:cursor-pointer data-disabled:opacity-50
        hover:not-data-disabled:bg-gray-50 dark:hover:not-data-disabled:bg-gray-950
        outline-none focus-visible:bg-gray-100 dark:focus-visible:bg-gray-950" data-ui-dropdown-item
    >
        <div v-if="icon" class="size-6 flex items-center justify-center p-1 text-gray-500">
            <ui-icon :name="icon" class="size-3.5!" />
        </div>
        <div class="col-start-2 ps-2">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
    </DropdownMenuItem>
</template>
